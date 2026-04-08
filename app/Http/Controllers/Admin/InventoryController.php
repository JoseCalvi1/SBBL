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
    public function index(Request $request)
    {
        $categories = InventoryCategory::orderBy('name')->get();
        $provinces = Province::orderBy('name')->get();
        // Solo traemos a usuarios que sean jueces, árbitros o admins para ser custodios
        $staffUsers = User::whereHas('roles', function($q) {
            $q->whereIn('name', ['admin', 'juez', 'arbitro']);
        })->orderBy('name')->get();

        // 1. INICIAMOS LA CONSULTA
        $query = InventoryItem::with(['category', 'province', 'custodian'])
            ->select('inventory_items.*'); // Seleccionamos solo los campos del item para evitar conflictos

        // 2. APLICAMOS FILTROS SI EXISTEN
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('inventory_items.name', 'like', '%' . $request->search . '%')
                  ->orWhere('inventory_items.brand', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('category_id')) {
            $query->where('inventory_items.category_id', $request->category_id);
        }
        if ($request->filled('province_id')) {
            $query->where('inventory_items.province_id', $request->province_id);
        }
        if ($request->filled('status')) {
            $query->where('inventory_items.status', $request->status);
        }

        // 3. ORDENACIÓN DINÁMICA POR COLUMNAS
        $sort = $request->get('sort', 'created_at');
        $dir = $request->get('dir', 'desc');

        if ($sort === 'name' || $sort === 'status') {
            $query->orderBy('inventory_items.' . $sort, $dir);
        } elseif ($sort === 'category') {
            $query->leftJoin('inventory_categories', 'inventory_items.category_id', '=', 'inventory_categories.id')
                  ->orderBy('inventory_categories.name', $dir);
        } elseif ($sort === 'province') {
            $query->leftJoin('provinces', 'inventory_items.province_id', '=', 'provinces.id')
                  ->orderBy('provinces.name', $dir);
        } else {
            $query->orderBy('inventory_items.created_at', 'desc');
        }

        // 4. PAGINACIÓN (15 registros por página manteniendo los filtros en la URL)
        $items = $query->paginate(15)->appends($request->all());

        // 5. ESTADÍSTICAS GLOBALES (Calculadas sobre el total, ignorando los filtros)
        $stats = (object)[
            'total' => InventoryItem::count(),
            'criticos' => InventoryItem::whereIn('status', ['critico', 'fuera_combate'])->count(),
            'operativos' => InventoryItem::whereIn('status', ['impecable', 'operativo'])->count(),
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
