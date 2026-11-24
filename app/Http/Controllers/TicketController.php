<?php

namespace App\Http\Controllers;

use App\Models\Raffle;
use App\Models\Ticket;
use App\Models\User;
use App\Services\Payment\PayPalService;
use App\Services\Payment\TreasuryService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TicketController extends Controller
{
    protected $paypalService;
    protected $treasuryService;

    public function __construct(PayPalService $paypalService, TreasuryService $treasuryService)
    {
        $this->paypalService = $paypalService;
        $this->treasuryService = $treasuryService;
    }

    /**
     * Muestra la página de compra de la rifa activa (solo usuarios autenticados).
     */
    public function showBuyForm()
    {
        $raffle = Raffle::where('is_active', true)->first();
        $paypalClientId = $this->paypalService->getClientId();
        $paypalCurrency = $this->paypalService->getCurrency();

        if (!$raffle) {
            return view('tickets.buy', [
                'raffle'          => null,
                'soldOut'         => false,
                'userTicketsCount'=> 0,
                'maxAllowedNow'   => 0,
                'availableTotal'  => null,
                'paypalClientId'  => $paypalClientId,
                'paypalCurrency'  => $paypalCurrency,
            ]);
        }

        $soldTickets = $raffle->tickets()->count();
        $userTicketsCount = Ticket::where('raffle_id', $raffle->id)
            ->where('user_id', Auth::id())
            ->count();

        $availableTotal = $raffle->max_tickets !== null
            ? max(0, $raffle->max_tickets - $soldTickets)
            : null;

        $remainingForUser = max(0, $raffle->max_per_user - $userTicketsCount);
        $maxAllowedNow = $availableTotal !== null
            ? min($availableTotal, $remainingForUser)
            : $remainingForUser;

        $soldOut = !$raffle->isOpenForPurchase();

        return view('tickets.buy', compact(
            'raffle',
            'soldTickets',
            'soldOut',
            'userTicketsCount',
            'maxAllowedNow',
            'availableTotal',
            'paypalClientId',
            'paypalCurrency'
        ));
    }

    /**
     * Crea la orden en PayPal Business para comprar papeletas de la rifa activa.
     */
    public function createOrder(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['error_details' => 'Debes iniciar sesión para comprar papeletas.'], 401);
        }

        $raffle = Raffle::where('is_active', true)->first();
        if (!$raffle || !$raffle->isOpenForPurchase()) {
            return response()->json([
                'error_details' => 'No hay ninguna rifa disponible para compra en este momento.',
            ], 422);
        }

        $request->validate([
            'quantity' => 'required|integer|min:1|max:' . max(1, $raffle->max_per_user),
        ]);

        $quantity = (int) $request->quantity;
        $user = Auth::user();

        if ($raffle->max_tickets !== null) {
            $soldTickets = $raffle->tickets()->count();
            $available = max(0, $raffle->max_tickets - $soldTickets);
            if ($quantity > $available) {
                return response()->json([
                    'error_details' => 'Papeletas insuficientes.',
                    'details'       => $available > 0
                        ? "Solo quedan {$available} papeletas disponibles."
                        : 'Las papeletas están agotadas.',
                ], 422);
            }
        }

        $userOwned = Ticket::where('raffle_id', $raffle->id)->where('user_id', $user->id)->count();
        if (($userOwned + $quantity) > $raffle->max_per_user) {
            $canBuy = max(0, $raffle->max_per_user - $userOwned);
            return response()->json([
                'error_details' => 'Límite por usuario alcanzado.',
                'details'       => "Ya tienes {$userOwned} papeleta(s). Puedes adquirir como máximo {$canBuy} más en esta rifa.",
            ], 422);
        }

        $total = round($quantity * $raffle->ticket_price, 2);
        $customId = "RAFFLE:{$raffle->id}:USER:{$user->id}:QTY:{$quantity}";
        $refId = "RIFA-{$raffle->id}-U{$user->id}";
        $desc = "{$raffle->title} - {$quantity} papeleta(s) ({$user->name})";

        try {
            $order = $this->paypalService->createOrder($total, $desc, $customId, $refId);
            return response()->json(['id' => $order['id']]);
        } catch (\Throwable $e) {
            Log::error('TicketController: Error creando orden PayPal de rifa', [
                'raffle_id' => $raffle->id,
                'user_id'   => $user->id,
                'error'     => $e->getMessage(),
            ]);
            return response()->json([
                'error_details' => 'No se pudo iniciar el pago en PayPal.',
                'details'       => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Captura y verifica en servidor el pago de PayPal Business, asigna las papeletas al usuario
     * y registra automáticamente el cobro en Tesorería.
     */
    public function captureOrder(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['status' => 'error', 'error' => 'Usuario no autenticado.'], 401);
        }

        $request->validate([
            'orderID' => 'required|string|max:128',
        ]);

        $orderId = $request->orderID;
        $user = Auth::user();

        // Idempotencia: si esta orden ya emitió sus papeletas, devolverlas sin duplicar
        $existingTickets = Ticket::where('paypal_order_id', $orderId)
            ->where('user_id', $user->id)
            ->get();

        if ($existingTickets->isNotEmpty()) {
            return response()->json([
                'status'  => 'success',
                'created' => $existingTickets->count(),
                'tickets' => $existingTickets->pluck('identifier')->values()->all(),
            ]);
        }

        try {
            $orderData = $this->paypalService->captureOrder($orderId);
            $verified = $this->paypalService->extractVerifiedCapture($orderData);

            // Validar metadatos firmados en la orden PayPal: RAFFLE:{id}:USER:{uid}:QTY:{qty}
            if (!preg_match('/^RAFFLE:(\d+):USER:(\d+):QTY:(\d+)$/', $verified['custom_id'], $matches)) {
                throw new \Exception('Metadatos de orden de rifa inválidos.');
            }

            $raffleId = (int) $matches[1];
            $orderUserId = (int) $matches[2];
            $quantity = (int) $matches[3];

            if ($orderUserId !== (int) $user->id) {
                throw new \Exception('Esta orden de pago pertenece a otro usuario.');
            }

            $createdIdentifiers = DB::transaction(function () use ($raffleId, $user, $quantity, $verified) {
                $raffle = Raffle::where('id', $raffleId)->lockForUpdate()->firstOrFail();

                $expectedAmount = round($quantity * $raffle->ticket_price, 2);
                if (abs($verified['gross_amount'] - $expectedAmount) > 0.01) {
                    throw new \Exception('El importe abonado no coincide con el coste de las papeletas.');
                }

                // Verificar de nuevo idempotencia dentro del bloqueo
                $already = Ticket::where('paypal_capture_id', $verified['capture_id'])->get();
                if ($already->isNotEmpty()) {
                    return $already->pluck('identifier')->values()->all();
                }

                if ($raffle->max_tickets !== null) {
                    $soldCount = Ticket::where('raffle_id', $raffle->id)->count();
                    if (($soldCount + $quantity) > $raffle->max_tickets) {
                        Log::critical('Rifa: Aforo superado justo durante captura PayPal', [
                            'raffle_id'  => $raffle->id,
                            'user_id'    => $user->id,
                            'capture_id' => $verified['capture_id'],
                        ]);
                        // Aun así registramos el ingreso en Tesorería marcado con solicitud de reembolso para no perder trazabilidad del dinero cobrado
                        $this->treasuryService->recordPayPalIncome([
                            'category'          => 'Rifa',
                            'gross_amount'      => $verified['gross_amount'],
                            'fee'               => $verified['fee'],
                            'net_amount'        => $verified['net_amount'],
                            'description'       => "EXCESO AFORO Rifa {$raffle->title} - {$user->name} (Reembolsar)",
                            'notes'             => 'El pago entró cuando se agotaron las papeletas. Reembolsar manualmente.',
                            'reference_id'      => $verified['capture_id'],
                            'paypal_order_id'   => $verified['order_id'],
                            'paypal_capture_id' => $verified['capture_id'],
                            'user_id'           => $user->id,
                            'raffle_id'         => $raffle->id,
                        ])->update(['refund_requested' => true]);

                        throw new \Exception('Las papeletas se agotaron mientras completabas el pago. Tu cobro ha quedado registrado para reembolso inmediato por Administración.');
                    }
                }

                $identifiers = [];
                for ($i = 0; $i < $quantity; $i++) {
                    $identifier = $this->generateIdentifier($raffle->ticket_prefix);
                    Ticket::create([
                        'user_id'           => $user->id,
                        'raffle_id'         => $raffle->id,
                        'identifier'        => $identifier,
                        'paypal_order_id'   => $verified['order_id'],
                        'paypal_capture_id' => $verified['capture_id'],
                        'price_paid'        => $raffle->ticket_price,
                    ]);
                    $identifiers[] = $identifier;
                }

                // Registro blindado en Tesorería
                $this->treasuryService->recordPayPalIncome([
                    'category'          => 'Rifa',
                    'gross_amount'      => $verified['gross_amount'],
                    'fee'               => $verified['fee'],
                    'net_amount'        => $verified['net_amount'],
                    'description'       => "Rifa «{$raffle->title}» ({$quantity} papeletas) - {$user->name}",
                    'reference_id'      => $verified['capture_id'],
                    'paypal_order_id'   => $verified['order_id'],
                    'paypal_capture_id' => $verified['capture_id'],
                    'user_id'           => $user->id,
                    'raffle_id'         => $raffle->id,
                ]);

                return $identifiers;
            });

            return response()->json([
                'status'  => 'success',
                'created' => count($createdIdentifiers),
                'tickets' => $createdIdentifiers,
            ]);
        } catch (\Throwable $e) {
            Log::error('TicketController: Error capturando orden de rifa', [
                'order_id' => $orderId,
                'user_id'  => $user->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'error'  => $e->getMessage(),
            ], 422);
        }
    }

    private function generateIdentifier(string $prefix): string
    {
        $cleanPrefix = strtoupper(preg_replace('/[^A-Z0-9]/', '', $prefix)) ?: 'RIFA';
        do {
            $num = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $id = $cleanPrefix . '-' . $num;
        } while (Ticket::where('identifier', $id)->exists());

        return $id;
    }

    /**
     * Vista de mis papeletas para el usuario logueado.
     */
    public function myTickets(Request $request)
    {
        $tickets = $request->user()
            ->tickets()
            ->with('raffle')
            ->latest()
            ->get();

        $activeRaffle = Raffle::where('is_active', true)->first();

        return view('tickets.mine', compact('tickets', 'activeRaffle'));
    }

    // ----------------------------------------------------------------------
    // SORTEO DE RIFA (EXCLUSIVO ADMIN)
    // ----------------------------------------------------------------------

    public function index(Request $request)
    {
        $raffles = Raffle::orderByDesc('is_active')->orderByDesc('created_at')->get();
        $selectedRaffle = $request->filled('raffle_id')
            ? Raffle::find($request->input('raffle_id'))
            : ($raffles->firstWhere('is_active', true) ?? $raffles->first());

        if (!$selectedRaffle) {
            return view('tickets.sorteo', [
                'raffles'           => $raffles,
                'selectedRaffle'    => null,
                'totalTickets'      => 0,
                'totalParticipants' => 0,
                'winners'           => [],
            ]);
        }

        $totalTickets = $selectedRaffle->tickets()->count();
        $totalParticipants = (int) $selectedRaffle->tickets()->distinct('user_id')->count('user_id');
        $winners = $selectedRaffle->winners_json ?? [];

        return view('tickets.sorteo', compact(
            'raffles',
            'selectedRaffle',
            'totalTickets',
            'totalParticipants',
            'winners'
        ));
    }

    public function draw(Request $request)
    {
        $request->validate([
            'raffle_id' => 'required|exists:raffles,id',
        ]);

        $raffle = Raffle::findOrFail($request->input('raffle_id'));
        $tickets = $raffle->tickets()->with('user')->get();
        $uniqueUsersCount = $tickets->pluck('user_id')->unique()->count();
        $neededWinners = max(1, (int) $raffle->winners_count);

        if ($uniqueUsersCount < $neededWinners) {
            return redirect()->route('tickets.sorteo', ['raffle_id' => $raffle->id])
                ->with('error', "Se necesitan al menos {$neededWinners} participantes distintos con papeletas para realizar el sorteo (actualmente hay {$uniqueUsersCount}).");
        }

        $pool = $tickets->all();
        $winners = [];

        for ($i = 0; $i < $neededWinners; $i++) {
            $pickedIndex = random_int(0, count($pool) - 1);
            $winnerTicket = $pool[$pickedIndex];
            $winnerUser = $winnerTicket->user;

            $winners[] = [
                'position' => $i + 1,
                'ticket'   => $winnerTicket->identifier,
                'user_id'  => $winnerTicket->user_id,
                'user'     => $winnerUser ? $winnerUser->name : 'Usuario #' . $winnerTicket->user_id,
            ];

            // Excluir el resto de papeletas de ese usuario para que no repita premio
            $pool = array_values(array_filter($pool, function ($t) use ($winnerTicket) {
                return (int) $t->user_id !== (int) $winnerTicket->user_id;
            }));
        }

        $raffle->update([
            'winners_json' => $winners,
            'drawn_at'     => now(),
            'is_active'    => false, // Al sortearla se cierra automáticamente la venta
        ]);

        return redirect()->route('tickets.sorteo', ['raffle_id' => $raffle->id])
            ->with('success', "¡Sorteo de «{$raffle->title}» ejecutado y guardado oficialmente!");
    }

    public function reset(Request $request)
    {
        $request->validate([
            'raffle_id' => 'required|exists:raffles,id',
        ]);

        $raffle = Raffle::findOrFail($request->input('raffle_id'));
        $raffle->update([
            'winners_json' => null,
            'drawn_at'     => null,
        ]);

        return redirect()->route('tickets.sorteo', ['raffle_id' => $raffle->id])
            ->with('success', 'Ganadores reiniciados correctamente.');
    }
}
