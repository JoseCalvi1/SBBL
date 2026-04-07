<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\InventoryItem;
use App\Models\InventoryCategory;
use App\Models\Province;
use App\Models\User;

class InventoryController extends Controller
{
    public function index()
    {
        $items = InventoryItem::with(['category', 'province', 'custodian'])->orderBy('province_id')->get();
        $categories = InventoryCategory::all();
        $provinces = Province::orderBy('name')->get();
        // Solo traemos a usuarios que sean jueces, árbitros o admins para ser custodios
        $staffUsers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'juez', 'arbitro']);
        })->orderBy('name')->get();

        // Estadísticas rápidas
        $stats = (object)[
            'total' => $items->count(),
            'criticos' => $items->whereIn('status', ['critico', 'fuera_combate'])->count(),
            'operativos' => $items->whereIn('status', ['impecable', 'operativo'])->count(),
        ];

        return view('admin.inventory.index', compact('items', 'categories', 'provinces', 'staffUsers', 'stats'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'category_id' => 'required|exists:inventory_categories,id',
            'province_id' => 'required|exists:provinces,id',
            'user_id' => 'nullable|exists:users,id',
            'name' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'status' => 'required|in:impecable,operativo,fatigado,critico,fuera_combate',
            'notes' => 'nullable|string'
        ]);

        InventoryItem::create($data);

        return back()->with('success', 'Material registrado en el arsenal correctamente.');
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $data = $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'status' => 'required|in:impecable,operativo,fatigado,critico,fuera_combate',
            'notes' => 'nullable|string'
        ]);

        $item->update($data);

        return back()->with('success', 'Estado del material actualizado.');
    }

    public function destroy($id)
    {
        InventoryItem::findOrFail($id)->delete();
        return back()->with('success', 'Material eliminado del registro.');
    }
}
