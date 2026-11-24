<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\TreasuryLog;
use App\Models\User;
use App\Services\Payment\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TreasuryController extends Controller
{
    protected $treasuryService;

    public function __construct(TreasuryService $treasuryService)
    {
        $this->treasuryService = $treasuryService;
    }

    public function index(Request $request)
    {
        $period = $request->input('period', 'active'); // active | archived | all

        $query = TreasuryLog::with(['user', 'event', 'raffle', 'carrito', 'creator']);

        if ($period === 'archived') {
            $query->archivedPeriod();
        } elseif ($period !== 'all') {
            $query->activePeriod();
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        if ($request->filled('source')) {
            $query->where('source', $request->input('source'));
        }

        if ($request->filled('status')) {
            if ($request->input('status') === 'refund_requested') {
                $query->where('refund_requested', true)->where('status', 'completado');
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        if ($request->filled('from')) {
            $query->whereDate('created_at', '>=', $request->input('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('created_at', '<=', $request->input('to'));
        }

        $logs = $query->latest()->get();

        // El saldo oficial siempre se calcula sobre el ejercicio activo (no archivado) y movimientos completados
        $activeCompleted = TreasuryLog::activePeriod()->where('status', 'completado')->get();

        $ingresosActivos = $activeCompleted->where('type', 'ingreso');
        $gastosActivos = $activeCompleted->where('type', 'gasto');

        $totalIngresosBrutos = (float) $ingresosActivos->sum('gross_amount');
        $totalComisionesPayPal = (float) $ingresosActivos->sum('fee');
        $totalIngresosNetos = (float) $ingresosActivos->sum('net_amount');
        $totalGastosNetos = (float) $gastosActivos->sum('net_amount');
        $saldoActual = round($totalIngresosNetos - $totalGastosNetos, 2);

        $pendingRefundsCount = TreasuryLog::activePeriod()
            ->where('refund_requested', true)
            ->where('status', 'completado')
            ->count();

        $archivedCount = TreasuryLog::archivedPeriod()->count();

        $categories = TreasuryLog::select('category')->distinct()->orderBy('category')->pluck('category');
        $events = Event::orderBy('date', 'desc')->take(40)->get();
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.treasury.index', compact(
            'logs',
            'period',
            'categories',
            'events',
            'users',
            'saldoActual',
            'totalIngresosBrutos',
            'totalComisionesPayPal',
            'totalIngresosNetos',
            'totalGastosNetos',
            'pendingRefundsCount',
            'archivedCount'
        ));
    }

    public function storeIncome(Request $request)
    {
        $data = $request->validate([
            'category'     => 'required|string|max:100',
            'gross_amount' => 'required|numeric|min:0.01',
            'fee'          => 'nullable|numeric|min:0',
            'description'  => 'required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
            'reference_id' => 'nullable|string|max:255',
            'user_id'      => 'nullable|exists:users,id',
            'event_id'     => 'nullable|exists:events,id',
            'receipt'      => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $fee = round((float) ($data['fee'] ?? 0), 2);
        $gross = round((float) $data['gross_amount'], 2);

        if ($fee > $gross) {
            return back()->withInput()->withErrors(['fee' => 'La comisión no puede superar el importe bruto recibido.']);
        }

        $receiptB64 = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $mimeType = $file->getMimeType();
            $receiptB64 = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        TreasuryLog::create([
            'type'         => 'ingreso',
            'source'       => 'manual',
            'category'     => $data['category'],
            'gross_amount' => $gross,
            'fee'          => $fee,
            'net_amount'   => round($gross - $fee, 2),
            'status'       => 'completado',
            'description'  => $data['description'],
            'notes'        => $data['notes'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'receipt_b64'  => $receiptB64,
            'user_id'      => $data['user_id'] ?? null,
            'event_id'     => $data['event_id'] ?? null,
            'created_by'   => Auth::id(),
        ]);

        return back()->with('success', 'Ingreso manual registrado y auditado en el Libro Mayor.');
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'category'     => 'required|string|max:100',
            'gross_amount' => 'required|numeric|min:0.01',
            'fee'          => 'nullable|numeric|min:0',
            'description'  => 'required|string|max:255',
            'notes'        => 'nullable|string|max:1000',
            'reference_id' => 'nullable|string|max:255',
            'user_id'      => 'nullable|exists:users,id',
            'event_id'     => 'nullable|exists:events,id',
            'receipt'      => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:4096',
        ]);

        $fee = round((float) ($data['fee'] ?? 0), 2);
        $gross = round((float) $data['gross_amount'], 2);

        $receiptB64 = null;
        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $mimeType = $file->getMimeType();
            $receiptB64 = 'data:' . $mimeType . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        TreasuryLog::create([
            'type'         => 'gasto',
            'source'       => 'manual',
            'category'     => $data['category'],
            'gross_amount' => $gross,
            'fee'          => $fee,
            'net_amount'   => round($gross + $fee, 2),
            'status'       => 'completado',
            'description'  => $data['description'],
            'notes'        => $data['notes'] ?? null,
            'reference_id' => $data['reference_id'] ?? null,
            'receipt_b64'  => $receiptB64,
            'user_id'      => $data['user_id'] ?? null,
            'event_id'     => $data['event_id'] ?? null,
            'created_by'   => Auth::id(),
        ]);

        return back()->with('success', 'Gasto registrado y justificante archivado correctamente.');
    }

    public function markRefunded(Request $request, TreasuryLog $log)
    {
        $data = $request->validate([
            'refund_note' => 'nullable|string|max:500',
        ]);

        if ($log->type !== 'ingreso') {
            return back()->withErrors(['refund' => 'Solo se pueden marcar como reembolsados los movimientos de ingreso.']);
        }

        if ($log->status === 'reembolsado') {
            return back()->with('success', 'Este movimiento ya figuraba como reembolsado.');
        }

        $this->treasuryService->markRefundedManually($log, Auth::id(), $data['refund_note'] ?? null);

        return back()->with('success', 'Ingreso marcado como reembolsado y descontado del saldo neto activo.');
    }

    public function resetPeriod(Request $request)
    {
        $data = $request->validate([
            'confirm_word'    => 'required|string',
            'initial_balance' => 'nullable|numeric|min:0',
            'reset_note'      => 'nullable|string|max:255',
        ]);

        if (trim(strtoupper($data['confirm_word'])) !== 'RESETEAR') {
            return back()->withErrors(['confirm_word' => 'Debes escribir exactamente la palabra RESETEAR para confirmar el corte de caja.']);
        }

        DB::transaction(function () use ($data) {
            TreasuryLog::whereNull('archived_at')->update([
                'archived_at' => now(),
            ]);

            $initial = isset($data['initial_balance']) ? round((float) $data['initial_balance'], 2) : 0.00;
            if ($initial > 0) {
                TreasuryLog::create([
                    'type'         => 'ingreso',
                    'source'       => 'manual',
                    'category'     => 'Saldo Inicial',
                    'gross_amount' => $initial,
                    'fee'          => 0.00,
                    'net_amount'   => $initial,
                    'status'       => 'completado',
                    'description'  => 'Saldo inicial tras cierre/reseteo de caja',
                    'notes'        => $data['reset_note'] ?? 'Apertura de nuevo periodo contable.',
                    'reference_id' => 'APERTURA-' . now()->format('Ymd-His'),
                    'created_by'   => Auth::id(),
                ]);
            }
        });

        return redirect()->route('admin.treasury.index')
            ->with('success', 'Corte contable ejecutado: los movimientos anteriores quedan archivados en BD y la caja inicia el nuevo ejercicio.');
    }

    public function exportCsv(Request $request)
    {
        $period = $request->input('period', 'active');
        $query = TreasuryLog::with(['user', 'event', 'raffle', 'carrito', 'creator']);

        if ($period === 'archived') {
            $query->archivedPeriod();
        } elseif ($period !== 'all') {
            $query->activePeriod();
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }
        if ($request->filled('status')) {
            if ($request->input('status') === 'refund_requested') {
                $query->where('refund_requested', true)->where('status', 'completado');
            } else {
                $query->where('status', $request->input('status'));
            }
        }

        $logs = $query->latest()->get();
        $filename = 'tesoreria_sbbl_' . $period . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($logs) {
            $out = fopen('php://output', 'w');
            // BOM UTF-8 para Excel
            fwrite($out, "\xEF\xBB\xBF");

            fputcsv($out, [
                'ID', 'Fecha', 'Periodo', 'Origen', 'Tipo', 'Categoria', 'Estado',
                'Bruto (EUR)', 'Comision (EUR)', 'Neto (EUR)', 'Concepto',
                'Referencia', 'PayPal Order ID', 'PayPal Capture ID',
                'Usuario', 'Evento / Rifa / Pedido', 'Auditado por', 'Notas'
            ], ';');

            foreach ($logs as $log) {
                $vinculo = [];
                if ($log->event) $vinculo[] = 'Evento: ' . $log->event->name;
                if ($log->raffle) $vinculo[] = 'Rifa: ' . $log->raffle->title;
                if ($log->carrito) $vinculo[] = 'Pedido: ' . $log->carrito->referencia;

                fputcsv($out, [
                    $log->id,
                    $log->created_at ? $log->created_at->format('d/m/Y H:i:s') : '',
                    $log->archived_at ? 'Archivado (' . $log->archived_at->format('d/m/Y') . ')' : 'Activo',
                    $log->source === 'paypal_auto' ? 'PayPal Automático' : 'Manual',
                    strtoupper($log->type),
                    $log->category,
                    strtoupper($log->status) . ($log->refund_requested ? ' (REEMBOLSO PENDIENTE)' : ''),
                    number_format($log->gross_amount, 2, ',', ''),
                    number_format($log->fee, 2, ',', ''),
                    number_format(($log->type === 'gasto' ? -$log->net_amount : $log->net_amount), 2, ',', ''),
                    $log->description,
                    $log->reference_id,
                    $log->paypal_order_id,
                    $log->paypal_capture_id,
                    $log->user ? $log->user->name : '',
                    implode(' | ', $vinculo),
                    $log->creator ? $log->creator->name : 'Sistema PayPal',
                    str_replace(["\r", "\n"], ' ', (string) $log->notes),
                ], ';');
            }

            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
