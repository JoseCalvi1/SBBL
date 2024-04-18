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

    public function show($id)
    {
        $article = Article::findOrFail($id); // Encontrar el artículo por su ID
        return view('blog.show', compact('article')); // Pasar el artículo a la vista
    }

    public function create()
    {
        return view('blog.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required',
            'description' => 'required',
            'article_type' => 'required',
        ]);

        Article::create([
            'title' => $request->title,
            'description' => $request->description,
            'article_type' => $request->article_type,
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
        ]);

        $article->update($request->all());

        return redirect()->route('blog.show', $article->id)->with('success', 'Artículo actualizado correctamente.');
    }
}
