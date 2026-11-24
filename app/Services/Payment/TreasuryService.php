<?php

namespace App\Services\Payment;

use App\Models\Carrito;
use App\Models\TreasuryLog;
use Illuminate\Support\Facades\DB;

class TreasuryService
{
    /**
     * Registra de forma atómica e idempotente un ingreso automático verificado por PayPal Business.
     */
    public function recordPayPalIncome(array $data): TreasuryLog
    {
        return DB::transaction(function () use ($data) {
            $captureId = $data['paypal_capture_id'] ?? null;
            $orderId = $data['paypal_order_id'] ?? null;
            $referenceId = $data['reference_id'] ?? $captureId ?? $orderId;

            $existing = null;

            if ($captureId) {
                $existing = TreasuryLog::where('paypal_capture_id', $captureId)->lockForUpdate()->first();
            }

            if (!$existing && $orderId) {
                $existing = TreasuryLog::where('paypal_order_id', $orderId)
                    ->orWhere('reference_id', $orderId)
                    ->lockForUpdate()
                    ->first();
            }

            $gross = round((float) ($data['gross_amount'] ?? 0), 2);
            $fee = round((float) ($data['fee'] ?? 0), 2);
            $net = isset($data['net_amount']) ? round((float) $data['net_amount'], 2) : round($gross - $fee, 2);

            if ($existing) {
                // Si estaba pendiente, lo cerramos con los datos verificados de PayPal; si ya estaba completado/reembolsado no duplicamos
                if ($existing->status === 'pendiente') {
                    $existing->update([
                        'source'            => 'paypal_auto',
                        'gross_amount'      => $gross,
                        'fee'               => $fee,
                        'net_amount'        => $net,
                        'status'            => 'completado',
                        'paypal_order_id'   => $orderId ?: $existing->paypal_order_id,
                        'paypal_capture_id' => $captureId ?: $existing->paypal_capture_id,
                        'reference_id'      => $referenceId ?: $existing->reference_id,
                        'description'       => $data['description'] ?? $existing->description,
                        'notes'             => $data['notes'] ?? $existing->notes,
                        'user_id'           => $data['user_id'] ?? $existing->user_id,
                        'event_id'          => $data['event_id'] ?? $existing->event_id,
                        'raffle_id'         => $data['raffle_id'] ?? $existing->raffle_id,
                        'carrito_id'        => $data['carrito_id'] ?? $existing->carrito_id,
                    ]);
                } elseif (!$existing->paypal_capture_id && $captureId) {
                    $existing->update(['paypal_capture_id' => $captureId]);
                }

                return $existing;
            }

            return TreasuryLog::create([
                'type'              => 'ingreso',
                'source'            => 'paypal_auto',
                'category'          => $data['category'] ?? 'PayPal Web',
                'gross_amount'      => $gross,
                'fee'               => $fee,
                'net_amount'        => $net,
                'status'            => 'completado',
                'description'       => $data['description'] ?? 'Cobro online PayPal Business',
                'notes'             => $data['notes'] ?? null,
                'reference_id'      => $referenceId,
                'paypal_order_id'   => $orderId,
                'paypal_capture_id' => $captureId,
                'user_id'           => $data['user_id'] ?? null,
                'event_id'          => $data['event_id'] ?? null,
                'raffle_id'         => $data['raffle_id'] ?? null,
                'carrito_id'        => $data['carrito_id'] ?? null,
            ]);
        });
    }

    /**
     * Marca en Tesorería que un participante con pago completado ha cancelado su plaza
     * para que Administración tramite el reembolso manual.
     */
    public function flagEventCancellationForRefund(int $eventId, int $userId): bool
    {
        $log = TreasuryLog::activePeriod()
            ->where('event_id', $eventId)
            ->where('user_id', $userId)
            ->where('type', 'ingreso')
            ->where('status', 'completado')
            ->latest()
            ->first();

        if (!$log) {
            return false;
        }

        $stamp = now()->format('d/m/Y H:i');
        $extraNote = "[{$stamp}] El jugador canceló asistencia en la web. Pendiente reembolso manual por Admin.";
        $notes = trim(($log->notes ? $log->notes . "\n" : '') . $extraNote);

        $log->update([
            'refund_requested' => true,
            'notes'            => $notes,
        ]);

        return true;
    }

    /**
     * Marca un movimiento como reembolsado automáticamente al recibir Webhook de PayPal.
     */
    public function markRefundedByCaptureId(string $captureId, ?string $refundId = null): ?TreasuryLog
    {
        return DB::transaction(function () use ($captureId, $refundId) {
            $log = TreasuryLog::where('paypal_capture_id', $captureId)
                ->orWhere('reference_id', $captureId)
                ->lockForUpdate()
                ->first();

            if (!$log) {
                return null;
            }

            $stamp = now()->format('d/m/Y H:i');
            $refundNote = "[{$stamp}] Reembolso confirmado automáticamente vía Webhook PayPal" . ($refundId ? " (ID: {$refundId})." : '.');
            $notes = trim(($log->notes ? $log->notes . "\n" : '') . $refundNote);

            $log->update([
                'status'           => 'reembolsado',
                'refund_requested' => false,
                'refunded_at'      => $log->refunded_at ?? now(),
                'notes'            => $notes,
            ]);

            if ($log->carrito_id) {
                Carrito::where('id', $log->carrito_id)->update(['estado_pago' => 'reembolsado']);
            }

            return $log;
        });
    }

    /**
     * Marca un movimiento como reembolsado manualmente por un Administrador desde el panel.
     */
    public function markRefundedManually(TreasuryLog $log, int $adminId, ?string $adminNote = null): TreasuryLog
    {
        return DB::transaction(function () use ($log, $adminId, $adminNote) {
            $stamp = now()->format('d/m/Y H:i');
            $line = "[{$stamp}] Marcado como reembolsado manualmente por Admin #{$adminId}." . ($adminNote ? " Nota: {$adminNote}" : '');
            $notes = trim(($log->notes ? $log->notes . "\n" : '') . $line);

            $log->update([
                'status'           => 'reembolsado',
                'refund_requested' => false,
                'refunded_at'      => now(),
                'notes'            => $notes,
            ]);

            if ($log->carrito_id) {
                Carrito::where('id', $log->carrito_id)->update(['estado_pago' => 'reembolsado']);
            }

            return $log;
        });
    }
}
