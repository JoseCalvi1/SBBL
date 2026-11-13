<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\User;
use App\Services\Discord\DiscordNotifier;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ArticleController extends Controller
{
    private DiscordNotifier $discordNotifier;

    public function __construct(?DiscordNotifier $discordNotifier = null)
    {
        $this->discordNotifier = $discordNotifier ?? app(DiscordNotifier::class);
    }

    public function index(Request $request)
    {
        $user = Auth::user();

        $query = Article::query();

        // Si el usuario NO es editor, se excluyen los borradores
        if (!$user || !$user->hasRole('editor')) {
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
        if (!$user || !$user->hasRole('editor')) {
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
        $article = Article::create([
            'title' => $request->title,
            'user_id' => Auth::user()->id,
            'description' => $request->description,
            'article_type' => $request->article_type,
            'custom_url' => $request->custom_url,
            'image' => $imageData,
        ]);

        $this->discordNotifier->sendArticleNotification($article, Auth::user());

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
        $this->discordNotifier->sendArticleNotification($articleId);
    }
}
