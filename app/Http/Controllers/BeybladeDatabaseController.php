<?php

namespace App\Http\Controllers;

use App\Models\AssistBlade;
use App\Models\Blade;
use App\Models\Ratchet;
use App\Models\Bit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Nette\Utils\Random;

class BeybladeDatabaseController extends Controller
{

    public function index()
    {
        return view('database.index');
    }

    public function listParts(Request $request)
    {
        $queryBlades = Blade::query();
        $queryRatchets = Ratchet::query();
        $queryBits = Bit::query();

        // Filtrar por marcas
        if ($request->filled('marca')) {
            $marcas = $request->marca;
            $queryBlades->where(function ($q) use ($marcas) {
                if (in_array('hasbro', $marcas)) {
                    $q->orWhere('marca_hasbro', true);
                }
                if (in_array('takara', $marcas)) {
                    $q->orWhere('marca_takara', true);
                }
            });

            $queryRatchets->where(function ($q) use ($marcas) {
                if (in_array('hasbro', $marcas)) {
                    $q->orWhere('marca_hasbro', true);
                }
                if (in_array('takara', $marcas)) {
                    $q->orWhere('marca_takara', true);
                }
            });

            $queryBits->where(function ($q) use ($marcas) {
                if (in_array('hasbro', $marcas)) {
                    $q->orWhere('marca_hasbro', true);
                }
                if (in_array('takara', $marcas)) {
                    $q->orWhere('marca_takara', true);
                }
            });
        }

        // Filtrar por tipo en los Blades
        if ($request->filled('tipo')) {
            $queryBlades->whereIn('tipo', $request->tipo);
        }

        // Filtrar por sistema en los Blades
        if ($request->filled('sistema')) {
            $queryBlades->whereIn('sistema', $request->sistema);
        }

        $blades = $queryBlades->orderBy('fecha_takara', 'DESC')->get();
        $ratchets = $queryRatchets->orderBy('fecha_takara', 'DESC')->get();
        $bits = $queryBits->orderBy('fecha_takara', 'DESC')->get();
        $sistemas = Blade::distinct()->pluck('sistema');

        return view('database.parts', compact('blades', 'ratchets', 'bits', 'sistemas'));
    }

    /**
     * Display a listing of the parts.
     *
     * @return \Illuminate\Http\Response
     */
    public function indexPartes()
    {
        $blades = Blade::all()->sortBy('nombre_takara');
        $assistBlades = AssistBlade::all()->sortBy('nombre');
        $ratchets = Ratchet::all()->sortBy('nombre');
        $bits = Bit::all()->sortBy('nombre');

        return view('database.indexPartes', compact('blades', 'assistBlades', 'ratchets', 'bits'));
    }

    /**
     * Store a newly created part.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'type' => 'required|in:Blade,Ratchet,Bit,AssistBlade',
        ]);

        $type = $request->type;
        $name = $request->nombre;

        try {
            switch ($type) {
                case 'Blade':
                    $part = Blade::create([
                        'nombre_takara' => $name,
                    ]);
                    break;
                case 'Ratchet':
                    $part = Ratchet::create([
                        'nombre' => $name,
                    ]);
                    break;
                case 'Bit':
                    $part = Bit::create([
                        'nombre' => $name,
                    ]);
                    break;
                    case 'AssistBlade':
                        $part = AssistBlade::create([
                            'nombre' => $name,
                        ]);
                        break;
            }

            return redirect()->back()->with('success', 'Parte creada con éxito!');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al crear la parte: ' . $e->getMessage()], 500);
        }
    }


    /**
     * Destroy a part.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        $type = $request->type;
        $part = null;

        switch ($type) {
            case 'blades':
                $part = Blade::findOrFail($id);
                break;
            case 'ratchets':
                $part = Ratchet::findOrFail($id);
                break;
            case 'bits':
                $part = Bit::findOrFail($id);
                break;
            case 'assistblades':
                $part = AssistBlade::findOrFail($id);
                break;
        }

        if ($part) {
            $part->delete();
            return redirect()->back()->with('success', 'Parte eliminada con éxito!');
        }

        return response()->json(['error' => 'Error al eliminar la parte.'], 500);
    }

    public function showBlade($id)
    {
        $blade = Blade::findOrFail($id);

        return view('database.showBlade', compact('blade'));
    }

    public function editBlade($id)
    {
        $blade = Blade::findOrFail($id);
        return view('database.editBlade', compact('blade'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre_takara' => 'nullable|string|max:255',
            'nombre_hasbro' => 'nullable|string|max:255',
            'tipo' => 'nullable|string',
            'color' => 'nullable|string|max:255',
            'giro' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'analisis' => 'nullable|string',
            'sistema' => 'nullable|string|max:255',
            'wave_hasbro' => 'nullable|string|max:255',
            'fecha_takara' => 'nullable|date',
            'imagen' => 'nullable|image|max:2048',
            'tarjeta' => 'nullable|image|max:2048',
        ]);

        $blade = Blade::findOrFail($id);

        // Asignar los valores manualmente para evitar que se ignoren valores nulos
        $blade->nombre_takara = $request->input('nombre_takara', '');
        $blade->nombre_hasbro = $request->input('nombre_hasbro', '');
        $blade->tipo = $request->input('tipo', '');
        $blade->color = $request->input('color', '');
        $blade->giro = $request->input('giro', '');
        $blade->descripcion = $request->input('descripcion', '');
        $blade->analisis = $request->input('analisis', '');
        $blade->sistema = $request->input('sistema', '');
        $blade->wave_hasbro = $request->input('wave_hasbro', '');
        $blade->fecha_takara = $request->input('fecha_takara');

        // Guardar los valores booleanos correctamente
        $blade->marca_takara = $request->has('marca_takara');
        $blade->marca_hasbro = $request->has('marca_hasbro');
        $blade->recolor = $request->has('recolor');

        // Manejo de imagen en base64 si se sube una nueva
        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $imageData = file_get_contents($image->getRealPath());
            $base64 = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
            $blade->imagen = $base64;
        }

        if ($request->hasFile('tarjeta')) {
            $image = $request->file('tarjeta');
            $imageData = file_get_contents($image->getRealPath());
            $base64 = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
            $blade->tarjeta = $base64;
        }

        $blade->save();

        return redirect()->route('database.indexPartes')->with('success', 'Blade actualizado correctamente.');
    }

    public function showRatchet($id)
    {
        $ratchet = Ratchet::findOrFail($id);

        return view('database.showRatchet', compact('ratchet'));
    }

    public function editRatchet($id)
    {
        $ratchet = Ratchet::findOrFail($id);
        return view('database.editRatchet', compact('ratchet'));
    }

    public function updateRatchet(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:255',
            'altura' => 'nullable|numeric',
            'num_salientes' => 'nullable|integer',
            'color' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'analisis' => 'nullable|string',
            'wave_hasbro' => 'nullable|string|max:255',
            'fecha_takara' => 'nullable|date',
            'imagen' => 'nullable|image|max:2048',
            'tarjeta' => 'nullable|image|max:2048',
        ]);

        $ratchet = Ratchet::findOrFail($id);

        $ratchet->nombre = $request->input('nombre', '');
        $ratchet->altura = $request->input('altura', null);
        $ratchet->num_salientes = $request->input('num_salientes', null);
        $ratchet->color = $request->input('color', '');
        $ratchet->descripcion = $request->input('descripcion', '');
        $ratchet->analisis = $request->input('analisis', '');
        $ratchet->marca_takara = $request->has('marca_takara');
        $ratchet->marca_hasbro = $request->has('marca_hasbro');
        $ratchet->recolor = $request->has('recolor');
        $ratchet->wave_hasbro = $request->input('wave_hasbro', '');
        $ratchet->fecha_takara = $request->input('fecha_takara', null);

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $imageData = file_get_contents($image->getRealPath());
            $base64 = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
            $ratchet->imagen = $base64;
        }

        if ($request->hasFile('tarjeta')) {
            $tarjeta = $request->file('tarjeta');
            $tarjetaData = file_get_contents($tarjeta->getRealPath());
            $base64Tarjeta = 'data:' . $tarjeta->getMimeType() . ';base64,' . base64_encode($tarjetaData);
            $ratchet->tarjeta = $base64Tarjeta;
        }

        $ratchet->save();

        return redirect()->route('database.indexPartes')->with('success', 'Ratchet actualizado correctamente.');
    }

    public function showBit($id)
    {
        $bit = Bit::findOrFail($id);

        return view('database.showBit', compact('bit'));
    }

    public function editBit($id)
    {
        $bit = Bit::findOrFail($id);
        return view('database.editBit', compact('bit'));
    }

    public function updateBit(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'nullable|string|max:255',
            'abreviatura' => 'nullable|string|max:255',
            'color' => 'nullable|string|max:255',
            'tipo' => 'nullable|string|max:255',
            'altura' => 'nullable|string|max:255',
            'descripcion' => 'nullable|string',
            'analisis' => 'nullable|string',
            'wave_hasbro' => 'nullable|string|max:255',
            'fecha_takara' => 'nullable|date',
            'imagen' => 'nullable|image|max:2048',
            'tarjeta' => 'nullable|image|max:2048',
        ]);

        $bit = Bit::findOrFail($id);

        $bit->nombre = $request->input('nombre', '');
        $bit->abreviatura = $request->input('abreviatura', '');
        $bit->color = $request->input('color', '');
        $bit->tipo = $request->input('tipo', '');
        $bit->altura = $request->input('altura', '');
        $bit->descripcion = $request->input('descripcion', '');
        $bit->analisis = $request->input('analisis', '');
        $bit->marca_takara = $request->has('marca_takara');
        $bit->marca_hasbro = $request->has('marca_hasbro');
        $bit->marca_hasbro = $request->has('recolor');
        $bit->wave_hasbro = $request->input('wave_hasbro', '');
        $bit->fecha_takara = $request->input('fecha_takara', null);

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $imageData = file_get_contents($image->getRealPath());
            $base64 = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
            $bit->imagen = $base64;
        }

        if ($request->hasFile('tarjeta')) {
            $tarjeta = $request->file('tarjeta');
            $tarjetaData = file_get_contents($tarjeta->getRealPath());
            $base64Tarjeta = 'data:' . $tarjeta->getMimeType() . ';base64,' . base64_encode($tarjetaData);
            $bit->tarjeta = $base64Tarjeta;
        }

        $bit->save();

        return redirect()->route('database.indexPartes')->with('success', 'Bit actualizado correctamente.');
    }


    public function indexBeys()
    {
        $beyblades = DB::table('beyblades')
            ->join('blades', 'beyblades.blade_id', '=', 'blades.id')
            ->join('ratchets', 'beyblades.ratchet_id', '=', 'ratchets.id')
            ->join('bits', 'beyblades.bit_id', '=', 'bits.id')
            ->select(
                'beyblades.*',
                'blades.nombre_takara as blade_nombre',
                'ratchets.nombre as ratchet_nombre',
                'bits.nombre as bit_nombre'
            )
            ->get();

        return view('database.indexBeys', compact('beyblades'));
    }

    public function listBeyblades(Request $request)
    {
        $query = DB::table('beyblades')
            ->join('blades', 'beyblades.blade_id', '=', 'blades.id')
            ->join('ratchets', 'beyblades.ratchet_id', '=', 'ratchets.id')
            ->join('bits', 'beyblades.bit_id', '=', 'bits.id')
            ->select(
                'beyblades.*',
                'blades.nombre_takara as blade_nombre',
                'ratchets.nombre as ratchet_nombre',
                'bits.nombre as bit_nombre'
            );

        // Filtrar por marcas
        if ($request->filled('marca')) {
            $marcas = $request->marca;

            $query->where(function ($q) use ($marcas) {
                if (in_array('hasbro', $marcas)) {
                    $q->orWhere('beyblades.marca_hasbro', true);
                }
                if (in_array('takara', $marcas)) {
                    $q->orWhere('beyblades.marca_takara', true);
                }
            });
        }

        // Filtrar por tipo de Beyblade
        if ($request->filled('tipo')) {
            $query->whereIn('beyblades.tipo', $request->tipo);
        }

        // Filtrar por sistema del Blade asociado
        if ($request->filled('sistema')) {
            $query->whereIn('blades.sistema', $request->sistema);
        }

        $beyblades = $query->orderBy('beyblades.id', 'DESC')->get();
        $sistemas = DB::table('blades')->distinct()->pluck('sistema');

        return view('database.beyblades', compact('beyblades', 'sistemas'));
    }

    public function createBey()
    {
        $blades = Blade::all()->sortBy('nombre_takara');
        $ratchets = Ratchet::all()->sortBy('nombre');
        $bits = Bit::all()->sortBy('nombre');

        return view('database.createBey', compact('blades', 'ratchets', 'bits'));
    }

    public function editBey($id)
    {
        $beyblade = DB::table('beyblades')
            ->where('id', $id)
            ->first();
        $blades = Blade::all()->sortBy('nombre_takara');
        $ratchets = Ratchet::all()->sortBy('nombre');
        $bits = Bit::all()->sortBy('nombre');

        return view('database.editBey', compact('beyblade', 'blades', 'ratchets', 'bits'));
    }

    public function storeBey(Request $request)
    {
        // Validación de datos
        $request->validate([
            'tipo' => 'required|in:Ataque,Balance,Defensa,Energía',
            'blade_id' => 'required|exists:blades,id',
            'ratchet_id' => 'required|exists:ratchets,id',
            'bit_id' => 'required|exists:bits,id',
            'descripcion' => 'nullable|string',
            'analisis' => 'nullable|string',
        ]);

        $base64 = "";
        $base64Tarjeta = "";

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $imageData = file_get_contents($image->getRealPath());
            $base64 = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
        }

        if ($request->hasFile('tarjeta')) {
            $tarjeta = $request->file('tarjeta');
            $tarjetaData = file_get_contents($tarjeta->getRealPath());
            $base64Tarjeta = 'data:' . $tarjeta->getMimeType() . ';base64,' . base64_encode($tarjetaData);
        }

        // Insertar en la base de datos
        DB::table('beyblades')->insert([
            'tipo' => $request->tipo,
            'blade_id' => $request->blade_id,
            'ratchet_id' => $request->ratchet_id,
            'bit_id' => $request->bit_id,
            'descripcion' => $request->descripcion,
            'analisis' => $request->analisis,
            'marca_takara' => $request->has('marca_takara') ? 1 : 0,
            'marca_hasbro' => $request->has('marca_hasbro') ? 1 : 0,
            'imagen' => $base64,
            'tarjeta' => $base64Tarjeta,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('database.indexBeys')->with('success', 'Beyblade creado correctamente');
    }

    public function updateBey(Request $request, $id)
    {
        // Validación de datos
        $request->validate([
            'tipo' => 'required|in:Ataque,Balance,Defensa,Energía',
            'blade_id' => 'required|exists:blades,id',
            'ratchet_id' => 'required|exists:ratchets,id',
            'bit_id' => 'required|exists:bits,id',
            'descripcion' => 'nullable|string',
            'analisis' => 'nullable|string',
        ]);

        // Obtener la Beyblade por ID
        $beyblade = DB::table('beyblades')->where('id', $id)->first();

        if (!$beyblade) {
            return redirect()->route('database.indexBeys')->with('error', 'Beyblade no encontrada');
        }

        // Revisar si hay una nueva imagen y tarjeta para actualizar
        $base64 = $beyblade->imagen; // Mantener la imagen actual si no se sube una nueva
        $base64Tarjeta = $beyblade->tarjeta; // Mantener la tarjeta actual si no se sube una nueva

        if ($request->hasFile('imagen')) {
            $image = $request->file('imagen');
            $imageData = file_get_contents($image->getRealPath());
            $base64 = 'data:' . $image->getMimeType() . ';base64,' . base64_encode($imageData);
        }

        if ($request->hasFile('tarjeta')) {
            $tarjeta = $request->file('tarjeta');
            $tarjetaData = file_get_contents($tarjeta->getRealPath());
            $base64Tarjeta = 'data:' . $tarjeta->getMimeType() . ';base64,' . base64_encode($tarjetaData);
        }

        // Actualizar en la base de datos
        DB::table('beyblades')->where('id', $id)->update([
            'tipo' => $request->tipo,
            'blade_id' => $request->blade_id,
            'ratchet_id' => $request->ratchet_id,
            'bit_id' => $request->bit_id,
            'descripcion' => $request->descripcion,
            'analisis' => $request->analisis,
            'marca_takara' => $request->has('marca_takara') ? 1 : 0,
            'marca_hasbro' => $request->has('marca_hasbro') ? 1 : 0,
            'imagen' => $base64,
            'tarjeta' => $base64Tarjeta,
            'updated_at' => now(),
        ]);

        return redirect()->route('database.indexBeys')->with('success', 'Beyblade actualizado correctamente');
    }

    public function showBey($id)
    {
        $beyblade = DB::table('beyblades')
            ->join('blades', 'beyblades.blade_id', '=', 'blades.id')
            ->join('ratchets', 'beyblades.ratchet_id', '=', 'ratchets.id')
            ->join('bits', 'beyblades.bit_id', '=', 'bits.id')
            ->select(
                'beyblades.*',
                'blades.id as blade_id',
                'blades.nombre_takara as blade_nombre',
                'blades.descripcion as blade_descripcion',
                'blades.imagen as blade_imagen',
                'blades.sistema as sistema',
                'ratchets.id as ratchet_id',
                'ratchets.nombre as ratchet_nombre',
                'ratchets.descripcion as ratchet_descripcion',
                'ratchets.imagen as ratchet_imagen',
                'bits.id as bit_id',
                'bits.nombre as bit_nombre',
                'bits.descripcion as bit_descripcion',
                'bits.imagen as bit_imagen'
            )
            ->where('beyblades.id', $id)
            ->first();

        // Verificar si la Beyblade existe
        if (!$beyblade) {
            return redirect()->route('database.indexBeys')->with('error', 'Beyblade no encontrada');
        }

        return view('database.showBey', compact('beyblade'));
    }




}
