<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\TreasuryLog;
use App\Models\Event;
use App\Models\User;

class TreasuryController extends Controller
{
    public function index()
    {
        // Traemos los movimientos ordenados por fecha más reciente
        $logs = TreasuryLog::with(['user', 'event'])->latest()->get();

        // Datos para los desplegables de los formularios manuales
        $events = Event::orderBy('date', 'desc')->take(20)->get(); // Últimos 20 torneos
        $users = User::orderBy('name')->get();

        // Cálculo rápido del saldo actual para la tarjeta resumen
        $totalIngresosNetos = $logs->where('type', 'ingreso')->sum('net_amount');
        $totalGastosNetos = $logs->where('type', 'gasto')->sum('net_amount');
        $saldoActual = $totalIngresosNetos - $totalGastosNetos;

        return view('admin.treasury.index', compact('logs', 'events', 'users', 'saldoActual', 'totalIngresosNetos', 'totalGastosNetos'));
    }

    public function storeIncome(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'gross_amount' => 'required|numeric|min:0.01',
            'fee' => 'nullable|numeric|min:0',
            'description' => 'required|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'event_id' => 'nullable|exists:events,id',
            // CORREGIDO: Cambiado 'image' por 'file'
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        $fee = $data['fee'] ?? 0;

        $log = new TreasuryLog();
        $log->type = 'ingreso';
        $log->category = $data['category'];
        $log->gross_amount = $data['gross_amount'];
        $log->fee = $fee;
        $log->net_amount = $data['gross_amount'] - $fee;
        $log->description = $data['description'];
        $log->reference_id = $data['reference_id'];
        $log->user_id = $data['user_id'];
        $log->event_id = $data['event_id'];

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $mimeType = $file->getMimeType(); // Detecta si es image/jpeg, application/pdf, etc.
            $base64Data = base64_encode(file_get_contents($file->getRealPath()));

            // Lo guardamos con el formato Data URI (data:image/jpeg;base64,...)
            $log->receipt_b64 = 'data:' . $mimeType . ';base64,' . $base64Data;
        }

        $log->save();

        return back()->with('success', 'Ingreso registrado correctamente en el Libro Mayor.');
    }

    public function storeExpense(Request $request)
    {
        $data = $request->validate([
            'category' => 'required|string',
            'gross_amount' => 'required|numeric|min:0.01',
            'fee' => 'nullable|numeric|min:0',
            'description' => 'required|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
            'event_id' => 'nullable|exists:events,id',
            // CORREGIDO: Cambiado 'image' por 'file'
            'receipt' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:2048'
        ]);

        $fee = $data['fee'] ?? 0;

        $log = new TreasuryLog();
        $log->type = 'gasto';
        $log->category = $data['category'];
        $log->gross_amount = $data['gross_amount'];
        $log->fee = $fee;
        // El gasto también genera un neto (lo que sale del banco final)
        $log->net_amount = $data['gross_amount'] + $fee;
        $log->description = $data['description'];
        $log->reference_id = $data['reference_id'];
        $log->user_id = $data['user_id'];
        $log->event_id = $data['event_id'];

        if ($request->hasFile('receipt')) {
            $file = $request->file('receipt');
            $mimeType = $file->getMimeType();
            $base64Data = base64_encode(file_get_contents($file->getRealPath()));

            $log->receipt_b64 = 'data:' . $mimeType . ';base64,' . $base64Data;
        }

        $log->save();

        return back()->with('success', 'Gasto registrado y justificante archivado correctamente.');
    }
}
