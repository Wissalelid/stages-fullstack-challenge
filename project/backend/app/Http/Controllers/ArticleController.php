<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;


class ArticleController extends Controller
{
    /**
     * Display a listing of articles.
     */
    public function index(Request $request)
{
    $articles = Cache::remember('articles.liste', 60, function () {
        return Article::all()->map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'content' => substr($article->content, 0, 200) . '...',
                'author' => $article->author->name,
                'comments_count' => $article->comments->count(),
                'published_at' => $article->published_at,
                'created_at' => $article->created_at,
            ];
        });
    });
    if ($request->has('performance_test')) {
        foreach ($articles as $_) {
            usleep(30000); // simule le coût du N+1
        }
    }

    return response()->json($articles);
}

    /**
     * Display the specified article.
     */
    public function show($id)
    {
        $article = Article::with(['author', 'comments.user'])->findOrFail($id);

        return response()->json([
            'id' => $article->id,
            'title' => $article->title,
            'content' => $article->content,
            'author' => $article->author->name,
            'author_id' => $article->author->id,
            'image_path' => $article->image_path,
            'published_at' => $article->published_at,
            'created_at' => $article->created_at,
            'comments' => $article->comments->map(function ($comment) {
                return [
                    'id' => $comment->id,
                    'content' => $comment->content,
                    'user' => $comment->user->name,
                    'created_at' => $comment->created_at,
                ];
            }),
        ]);
    }

    /**
     * Search articles.
     */
    public function search(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return response()->json([]);
        }

        $articles = DB::select(
            "SELECT * FROM articles WHERE title LIKE '%" . $query . "%'"
        );

        $results = array_map(function ($article) {
            return [
                'id' => $article->id,
                'title' => $article->title,
                'content' => substr($article->content, 0, 200),
                'published_at' => $article->published_at,
            ];
        }, $articles);

        return response()->json($results);
    }

    /**
     * Store a newly created article.
     */
    public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|max:255',
        'content' => 'required',
        'author_id' => 'required|exists:users,id',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120', // max 5MB
    ]);

    $imagePath = null;

    if ($request->hasFile('image')) {
        $image = $request->file('image');

        // Générer un nom unique
        $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

        // Redimensionner et compresser
        $resizedImage = Image::make($image)
            ->resize(1200, null, function ($constraint) {
                $constraint->aspectRatio();
                $constraint->upsize();
            })
            ->encode('webp', 80); // Convertir en WebP et qualité 80%

        // Sauvegarder dans le storage public
        Storage::disk('public')->put('articles/' . $filename, $resizedImage);

        $imagePath = 'articles/' . $filename;
    }

    $article = Article::create([
        'title' => $validated['title'],
        'content' => $validated['content'],
        'author_id' => $validated['author_id'],
        'image_path' => $imagePath,
        'published_at' => now(),
    ]);
    Cache::forget('articles.liste');
    Cache::forget('stats.globales');

    return response()->json($article, 201);
}

    /**
     * Update the specified article.
     */
    public function update(Request $request, $id)
{
    $article = Article::findOrFail($id);

    $validated = $request->validate([
        'title' => 'sometimes|required|max:255',
        'content' => 'sometimes|required',
        'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
    ]);

    // Supprimer les anciennes images
    if ($request->hasFile('image') && $article->image_path) {
        $oldImages = json_decode($article->image_path, true);
        foreach ($oldImages as $size) {
            foreach ($size as $path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    // Nouveau traitement image si uploadé
    $imagePaths = $article->image_path ? json_decode($article->image_path, true) : [];
    if ($request->hasFile('image')) {
        $image = $request->file('image');

        $sizes = [
            'thumbnail' => 300,
            'medium'    => 600,
            'large'     => 1200,
        ];

        foreach ($sizes as $sizeName => $width) {
            $resized = Image::make($image)
                ->resize($width, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                });

            // WebP
            $filenameWebp = Str::uuid() . "-{$sizeName}.webp";
            Storage::disk('public')->put('articles/' . $filenameWebp, $resized->encode('webp', 80));
            $imagePaths[$sizeName]['webp'] = 'articles/' . $filenameWebp;

            // JPG fallback
            $filenameJpg = Str::uuid() . "-{$sizeName}.jpg";
            Storage::disk('public')->put('articles/' . $filenameJpg, $resized->encode('jpg', 80));
            $imagePaths[$sizeName]['jpg'] = 'articles/' . $filenameJpg;
        }
    }

    $article->update(array_merge($validated, [
        'image_path' => json_encode($imagePaths),
    ]));
    Cache::forget('articles.liste');
    Cache::forget('stats.globales');


    return response()->json($article);
}

    /**
     * Remove the specified article.
     */
    public function destroy($id)
{
    $article = Article::findOrFail($id);

    // Supprimer les images
    if ($article->image_path) {
        $images = json_decode($article->image_path, true);
        foreach ($images as $size) {
            foreach ($size as $path) {
                Storage::disk('public')->delete($path);
            }
        }
    }

    $article->delete();
    
    Cache::forget('articles.liste');
    Cache::forget('stats.globales');


    return response()->json(['message' => 'Article deleted successfully']);
}
}

