<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class ArticleController extends Controller
{

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Article::query();

        // Si el usuario NO es editor, se excluyen los borradores
        if (!$user || !$user->is_editor) {
            $query->where('article_type', '!=', 'Borrador');
        }

        // Filtro por tipo
        if ($request->filled('type')) {
            $query->where('article_type', $request->type);
        }

        // Filtro por fechas
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $articles = $query->orderBy('id', 'DESC')->get();

        // Mostrar tipos de post disponibles en el filtro
        $typesQuery = Article::select('article_type')->distinct();
        if (!$user || !$user->is_editor) {
            $typesQuery->where('article_type', '!=', 'Borrador');
        }
        $types = $typesQuery->pluck('article_type')->sort();

        return view('blog.index', compact('articles', 'types'));
    }



    public function show($custom_url)
    {
        $article = Article::where('custom_url', $custom_url)->firstOrFail();
        return view('blog.show', compact('article')); // Pasar el artículo a la vista
    }

    public function create()
    {
        return view('blog.create');
    }

    public function store(Request $request)
    {

        if ($request->hasFile('image')) {
            $request->validate([
                'image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);
        }

        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'article_type' => 'required',
            'custom_url' => 'nullable|string|unique:articles,custom_url',
        ]);

        if ($request->hasFile('image')) {
            $imageData = base64_encode(file_get_contents($request->file('image')));
        } else {
            $imageData = null;
        }
        $articleId = Article::create([
            'title' => $request->title,
            'user_id' => Auth::user()->id,
            'description' => $request->description,
            'article_type' => $request->article_type,
            'custom_url' => $request->custom_url,
            'image' => $imageData,
        ]);

        // TODO Comentar para probar en local
        //Self::notification(Article::find($articleId));

        return redirect()->route('blog.index')->with('success', '¡Artículo creado exitosamente!');
    }

    public function edit(Article $article)
    {
        return view('blog.edit', compact('article'));
    }

    public function update(Request $request, Article $article)
{
    $request->validate([
        'title' => 'required',
        'description' => 'required',
        'article_type' => 'required',
        'custom_url' => 'nullable|string|unique:articles,custom_url,' . $article->id,
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
    ]);

    // Almacenar la imagen actual antes de actualizar el artículo
    $currentImage = $article->image;

    if ($request->hasFile('image')) {
        $imageData = base64_encode(file_get_contents($request->file('image')));
        $article->image = $imageData;
    }

    $article->update($request->except('image')); // Excluir el campo de imagen al actualizar el artículo

    // Restaurar la imagen actual si el formulario se envía sin seleccionar un nuevo archivo de imagen
    if (!$request->hasFile('image')) {
        $article->image = $currentImage;
        $article->save();
    }

    return redirect()->route('blog.show', $article->custom_url)->with('success', 'Artículo actualizado correctamente.');
}


public function notification($articleId)
    {
        $user = User::find(Auth::user()->id);

        // Construyes el mensaje
        $message = "¡Hay un nuevo anuncio de compra/venta!";

        // Envías el mensaje al webhook de Discord
        return Http::post('https://discord.com/api/webhooks/1293511125538832405/8K0_bqpwuIpwcPedfhETr8fOpOBsOKMMZONy-21_iezWUn2MXMA23dxBQtWVMUls1WQ_', [
            'content' => $message,
            'embeds' => [
                [
                    'title' => $user->name . " ha publicado un nuevo anuncio de " . $articleId[0]->article_type,
                    'description' => $articleId[0]->title . ". Accede en: https://sbbl.es/mercado/" . $articleId[0]->custom_url,
                    'color' => '7506394',
                ]
            ],
        ]);
    }

}
