<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Raffle;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RaffleController extends Controller
{
    public function index()
    {
        $raffles = Raffle::withCount('tickets')
            ->orderByDesc('is_active')
            ->orderByDesc('created_at')
            ->get();

        return view('admin.raffles.index', compact('raffles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:3000',
            'prizes_info'   => 'nullable|string|max:3000',
            'ticket_price'  => 'required|numeric|min:0.50|max:500',
            'max_tickets'   => 'nullable|integer|min:1',
            'max_per_user'  => 'required|integer|min:1|max:500',
            'ticket_prefix' => 'required|alpha_num|max:16',
            'winners_count' => 'required|integer|min:1|max:20',
            'ends_at'       => 'nullable|date',
            'is_active'     => 'nullable|boolean',
            'banner'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $bannerB64 = null;
        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $bannerB64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        $activate = $request->boolean('is_active');

        DB::transaction(function () use ($data, $bannerB64, $activate) {
            if ($activate) {
                Raffle::where('is_active', true)->update(['is_active' => false]);
            }

            Raffle::create([
                'title'         => $data['title'],
                'description'   => $data['description'] ?? null,
                'prizes_info'   => $data['prizes_info'] ?? null,
                'banner_b64'    => $bannerB64,
                'ticket_price'  => round((float) $data['ticket_price'], 2),
                'max_tickets'   => $data['max_tickets'] ?? null,
                'max_per_user'  => (int) $data['max_per_user'],
                'ticket_prefix' => strtoupper($data['ticket_prefix']),
                'winners_count' => (int) $data['winners_count'],
                'is_active'     => $activate,
                'ends_at'       => $data['ends_at'] ?? null,
                'created_by'    => Auth::id(),
            ]);
        });

        return back()->with('success', 'Rifa creada correctamente.' . ($activate ? ' Ya está activa en la web (las demás quedaron desactivadas).' : ''));
    }

    public function update(Request $request, Raffle $raffle)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'description'   => 'nullable|string|max:3000',
            'prizes_info'   => 'nullable|string|max:3000',
            'ticket_price'  => 'required|numeric|min:0.50|max:500',
            'max_tickets'   => 'nullable|integer|min:1',
            'max_per_user'  => 'required|integer|min:1|max:500',
            'ticket_prefix' => 'required|alpha_num|max:16',
            'winners_count' => 'required|integer|min:1|max:20',
            'ends_at'       => 'nullable|date',
            'banner'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $raffle->banner_b64 = 'data:' . $file->getMimeType() . ';base64,' . base64_encode(file_get_contents($file->getRealPath()));
        }

        $raffle->fill([
            'title'         => $data['title'],
            'description'   => $data['description'] ?? null,
            'prizes_info'   => $data['prizes_info'] ?? null,
            'ticket_price'  => round((float) $data['ticket_price'], 2),
            'max_tickets'   => $data['max_tickets'] ?? null,
            'max_per_user'  => (int) $data['max_per_user'],
            'ticket_prefix' => strtoupper($data['ticket_prefix']),
            'winners_count' => (int) $data['winners_count'],
            'ends_at'       => $data['ends_at'] ?? null,
        ]);

        $raffle->save();

        return back()->with('success', "Rifa «{$raffle->title}» actualizada.");
    }

    public function toggleActive(Raffle $raffle)
    {
        DB::transaction(function () use ($raffle) {
            $newState = !$raffle->is_active;
            if ($newState) {
                // Regla: rifas de una en una
                Raffle::where('id', '!=', $raffle->id)->update(['is_active' => false]);
            }
            $raffle->update(['is_active' => $newState]);
        });

        return back()->with(
            'success',
            $raffle->fresh()->is_active
                ? "La rifa «{$raffle->title}» ahora está ACTIVA como única rifa en venta."
                : "La rifa «{$raffle->title}» ha sido pausada."
        );
    }

    public function showTickets(Raffle $raffle)
    {
        $tickets = $raffle->tickets()->with('user')->latest()->get();
        return view('admin.raffles.tickets', compact('raffle', 'tickets'));
    }
}
