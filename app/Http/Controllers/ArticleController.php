<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;

class ArticleController extends Controller
{
    public function index()
    {
        $articles = Article::all(); // Obtener todos los artículos
        return view('blog.index', compact('articles')); // Pasar los artículos a la vista
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
                'image' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
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

        Article::create([
            'title' => $request->title,
            'description' => $request->description,
            'article_type' => $request->article_type,
            'custom_url' => $request->custom_url,
            'image' => $imageData,
        ]);

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



}
