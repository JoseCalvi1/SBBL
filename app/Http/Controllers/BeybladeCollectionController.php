<?php

namespace App\Http\Controllers;

use App\Models\AssistBlade;
use App\Models\BeybladeCollection;
use App\Models\Bit;
use App\Models\Blade;
use App\Models\Ratchet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use function PHPSTORM_META\type;

class BeybladeCollectionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Traemos todas las piezas, ordenadas por nombre
        $blades = Blade::orderBy('nombre_takara')->get()->toArray();
        $assist_blades = AssistBlade::orderBy('nombre')->get()->toArray();
        $ratchets = Ratchet::orderBy('nombre')->get()->toArray();
        $bits = Bit::orderBy('nombre')->get()->toArray();


        $myBlades = BeybladeCollection::where('type', 'Blade')
            ->where('user_id', Auth::user()->id)
            ->with('partBlade')
            ->get()
            ->sortBy(function ($item) {
                return $item->partBlade->nombre_takara ?? '';
            });

        $myRatchets = BeybladeCollection::where('type', 'Ratchet')
            ->where('user_id', Auth::user()->id)
            ->with('partRatchet')
            ->get()
            ->sortBy(function ($item) {
                return $item->partRatchet->nombre ?? '';
            });

        $myBits = BeybladeCollection::where('type', 'Bit')
            ->where('user_id', Auth::user()->id)
            ->with('partBit')
            ->get()
            ->sortBy(function ($item) {
                return $item->partBit->nombre ?? '';
            });

        $myAssistBlades = BeybladeCollection::where('type', 'Assist Blade')
            ->where('user_id', Auth::user()->id)
            ->with('partAssistBlade')
            ->get()
            ->sortBy(function ($item) {
                return $item->partAssistBlade->nombre ?? '';
            });


        // Pasamos todos los datos a la vista
        return view('database.collection', compact('blades', 'assist_blades', 'ratchets', 'bits', 'myBlades', 'myRatchets', 'myBits', 'myAssistBlades'));
    }




    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'part_id' => 'required',
            'weight' => 'nullable|numeric',
            'color' => 'nullable|string',
            'comment' => 'nullable|string',
            'quantity' => 'nullable|numeric',
            'type' => 'required|string|in:Blade,Ratchet,Bit,Assist Blade',
        ]);

        BeybladeCollection::create([
            'user_id' => Auth::user()->id, // Asumiendo que está logueado
            'part_id' => $request->part_id,
            'weight' => $request->weight,
            'color' => $request->color,
            'comment' => $request->comment,
            'quantity' => $request->quantity,
            'type' => $request->type,
        ]);

        return redirect()->back()->with('success', 'Pieza añadida correctamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\BeybladeCollection  $beybladeCollection
     * @return \Illuminate\Http\Response
     */
    public function show(BeybladeCollection $beybladeCollection)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\BeybladeCollection  $beybladeCollection
     * @return \Illuminate\Http\Response
     */
    public function edit(BeybladeCollection $beybladeCollection)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\BeybladeCollection  $beybladeCollection
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // Validar si es necesario (Recomendado)
        $request->validate([
            'part_id' => 'required|integer', // Asegúrate de que sea requerido
            'weight' => 'nullable|numeric',
            'color' => 'nullable|string',
            'quantity' => 'nullable|integer',
            'comment' => 'nullable|string|max:255',
            'type' => 'required|string', // Aunque no se guarda, es bueno validarlo
        ]);

        $item = BeybladeCollection::findOrFail($id);

        // **CORRECCIÓN: Incluir part_id**
        $item->part_id = $request->part_id;

        $item->weight = $request->weight;
        $item->color = $request->color;
        $item->quantity = $request->quantity;
        $item->comment = $request->comment;

        $item->save();

        return redirect()->back()->with('success', 'Pieza actualizada correctamente');
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\BeybladeCollection  $beybladeCollection
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $collection = BeybladeCollection::where('user_id', Auth::user()->id)->findOrFail($id);
        $collection->delete();

        return redirect()->back()->with('success', 'Pieza eliminada de tu colección.');
    }

}
