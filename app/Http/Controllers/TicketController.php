<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TicketController extends Controller
{
    // Muestra el formulario de compra
    public function showBuyForm()
    {
        if (!env('PAYPAL_LIVE_CLIENT_ID')) {
            abort(500, 'PAYPAL_LIVE_CLIENT_ID no configurado en el entorno.');
        }
        return view('tickets.buy');
    }

    // Crea la orden de PayPal
    public function createOrder(Request $request)
    {
        $request->validate(['quantity' => 'required|integer|min:1|max:1000']);
        $quantity = (int) $request->quantity;
        $unitPrice = 3.00;
        $total = number_format($quantity * $unitPrice, 2, '.', '');

        $token = $this->getPaypalAccessToken();
        if (!$token) {
            return response()->json([
                'error_details' => 'No se pudo obtener el token de PayPal para crear la orden.',
                'details' => 'Revisa credenciales de API o conexión.'
            ], 200);
        }

        $base = $this->paypalBaseUrl();

        $response = Http::withToken($token)
            ->timeout(30)
            ->asJson() // Aseguramos que se envíe como JSON
            ->post("{$base}/v2/checkout/orders", [
                'intent' => 'CAPTURE',
                'purchase_units' => [
                    [
                        'amount' => [
                            'currency_code' => 'EUR',
                            'value' => $total
                        ],
                        'custom_id' => (string) $quantity,
                        'description' => "Sorteo Navidad - {$quantity} ticket(s) x 3€"
                    ]
                ],
                'application_context' => [
                    'brand_name' => config('app.name'),
                    'locale' => 'es-ES',
                    'shipping_preference' => 'NO_SHIPPING',
                    'user_action' => 'PAY_NOW'
                ]
            ]);

        if ($response->failed()) {
            $details = $response->json() ?? ['message' => 'Error de conexión desconocido'];
            Log::error('PAYPAL CREATE ORDER FAILED', [
                'status' => $response->status(),
                'response_body' => $details,
            ]);
            return response()->json([
                'error_details' => 'Error al crear la orden en PayPal (Servidor).',
                'details' => $details['message'] ?? 'Verifica logs para detalles del error.',
                'status_code' => $response->status()
            ], 200);
        }

        return response()->json($response->json());
    }

    /**
     * Captura el pago de PayPal y crea los tickets.
     * FIX CRÍTICO: Envía explícitamente el cuerpo como el string JSON '{}'.
     */
    public function captureOrder(Request $request)
    {
        if (!auth()->check()) {
            return response()->json(['error' => 'Usuario no autenticado.'], 401);
        }

        $request->validate(['orderID' => 'required|string']);

        $orderId = $request->orderID;
        $token = $this->getPaypalAccessToken();

        if (! $token) {
            return response()->json(['status' => 'error', 'error' => 'No se pudo obtener token de PayPal para CAPTURA. Revise credenciales.'], 200);
        }

        // Ejecutar la captura usando cURL para garantizar el cuerpo JSON vacío
        $response = $this->executePaypalCaptureCurl($orderId, $token);

        // Si la respuesta falló (código de estado 400 o superior)
        if (data_get($response, 'status_code') >= 400) {
            $details = data_get($response, 'body');

            Log::error('PAYPAL CAPTURE FAILED', [
                'order_id' => $orderId,
                'status_code' => data_get($response, 'status_code'),
                'paypal_response_body' => $details,
            ]);

            return response()->json([
                'status' => 'error',
                'error' => 'Error al capturar la orden en PayPal. ',
                'status_code' => data_get($response, 'status_code'),
                'paypal_name' => data_get($details, 'name') ?? 'Error desconocido.',
                'details' => data_get($details, 'message') ?? 'Verifica logs para más detalles. Error de formato.',
            ], 200);
        }

        // --- Procesamiento de Éxito ---
        $data = data_get($response, 'body');

        // 1. Verificar estado de la orden
        $status = data_get($data, 'status');
        if ($status !== 'COMPLETED') {
            Log::warning('PAYPAL CAPTURE NOT COMPLETED', ['order_id' => $orderId, 'status' => $status]);
            return response()->json([
                'status' => 'error',
                'error' => "Pago no completado por PayPal. Estado: {$status}"
            ], 200);
        }

        // 2. Obtener cantidad de tickets
        $quantity = (int) data_get($data, 'purchase_units.0.custom_id', 0);

        if ($quantity <= 0) {
            $amount = (float) data_get($data, 'purchase_units.0.payments.captures.0.amount.value', 0);
            $quantity = (int) round($amount / 3);
        }

        if ($quantity <= 0) {
            return response()->json([
                'status' => 'error',
                'error' => 'Cantidad inválida en la orden o error de cálculo'
            ], 200);
        }

        $user = $request->user();

        // 3. Crear tickets dentro de transacción
        DB::beginTransaction();
        try {
            $tickets = [];
            for ($i = 0; $i < $quantity; $i++) {
                $identifier = $this->generateIdentifier();
                $tickets[] = Ticket::create([
                    'user_id' => $user->id,
                    'identifier' => $identifier
                ]);
            }
            DB::commit();

            return response()->json([
                'status' => 'success',
                'created' => count($tickets),
                'tickets' => array_map(fn($t) => $t->identifier, $tickets)
            ], 200);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('DB TICKET CREATION FAILED', ['error' => $e->getMessage()]);
            return response()->json([
                'status' => 'error',
                'error' => 'Error creando tickets (DB): '.$e->getMessage()
            ], 200);
        }
    }

    // ----------------------------------------------------------------------
    // HELPERS
    // ----------------------------------------------------------------------

    // Obtener la URL base (Sandbox o Live)
    private function paypalBaseUrl()
    {
        return env('PAYPAL_MODE') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    // Obtener el Token de Acceso de PayPal
    private function getPaypalAccessToken()
    {
        $client = env('PAYPAL_LIVE_CLIENT_ID');
        $secret = env('PAYPAL_LIVE_CLIENT_SECRET');

        if (! $client || ! $secret) {
            Log::error('PAYPAL CREDENTIALS MISSING', ['client' => $client, 'secret_provided' => !empty($secret)]);
            return null;
        }

        $base = $this->paypalBaseUrl();

        $res = Http::withBasicAuth($client, $secret)
            ->timeout(30)
            ->asForm()
            ->post("{$base}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

        if ($res->failed()) {
            Log::error('PAYPAL ACCESS TOKEN FAILED', ['status' => $res->status(), 'body' => $res->body(), 'base_url' => $base]);
            return null;
        }

        return data_get($res->json(), 'access_token');
    }

    // Generar identificador de ticket
    private function generateIdentifier()
    {
        do {
            $num = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $id = 'NAV' . now()->year . '-' . $num;
        } while (Ticket::where('identifier', $id)->exists());

        return $id;
    }

    // Vista de mis tickets
    public function myTickets(Request $request)
    {
        $tickets = $request->user()->tickets()->latest()->get();
        return view('tickets.mine', compact('tickets'));
    }

    private function executePaypalCaptureCurl(string $orderId, string $token)
    {
        $base = $this->paypalBaseUrl();
        $url = "{$base}/v2/checkout/orders/{$orderId}/capture";

        $ch = curl_init($url);

        // Cabeceras estrictas
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: 2', // Debe ser 2, la longitud de "{}"
            'Authorization: Bearer ' . $token,
        ]);

        // Cuerpo y método
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, '{}'); // El cuerpo es la cadena literal "{}"

        // Configuración de respuesta
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $responseBody = curl_exec($ch);
        $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {
            Log::error('PAYPAL CAPTURE CURL FAILED', ['curl_error' => $error]);
            // Devolvemos un error 500 para la lógica principal
            return ['status_code' => 500, 'body' => ['message' => 'Error de conexión cURL: ' . $error]];
        }

        // Devolvemos el cuerpo decodificado y el código de estado
        return [
            'status_code' => $statusCode,
            'body' => json_decode($responseBody, true)
        ];
    }

    public function index(Request $request)
    {

        // Obtener todos los tickets
        $tickets = Ticket::all()->toArray();
        $totalTickets = count($tickets);
        $totalParticipants = User::whereIn('id', Ticket::pluck('user_id'))->count();

        $winners = $request->session()->get('winners', []);

        return view('tickets.sorteo', [
            'totalTickets' => $totalTickets,
            'totalParticipants' => $totalParticipants,
            'winners' => $winners
        ]);
    }

    public function draw(Request $request)
    {
        // Limpiar los ganadores anteriores
        $request->session()->forget('winners');

        $tickets = Ticket::all()->toArray();

        if(count($tickets) < 3){
            return redirect()->back()->with('error', 'No hay suficientes papeletas para la rifa');
        }

        $winners = [];
        $availableTickets = $tickets;

        for($i=0; $i<3; $i++){
            $winnerTicket = $availableTickets[array_rand($availableTickets)];
            $user = User::find($winnerTicket['user_id']);
            $winners[] = [
                'ticket' => $winnerTicket['identifier'],
                'user' => $user->name
            ];
            // Quitar todas las papeletas del usuario ganador
            $availableTickets = array_filter($availableTickets, fn($t) => $t['user_id'] != $winnerTicket['user_id']);
            $availableTickets = array_values($availableTickets);
        }

        // Guardar ganadores en sesión
        $request->session()->put('winners', $winners);

        return redirect()->route('tickets.sorteo');
    }

    public function reset(Request $request)
    {
        // Borrar los ganadores de la sesión
        $request->session()->forget('winners');

        // Redirigir de nuevo al sorteo
        return redirect()->route('tickets.sorteo')->with('success', 'Rifa reiniciada correctamente.');
    }



}
