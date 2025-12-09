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
        // Version PERF-002 avec CACHE
        $articles = Cache::remember('articles.liste', 60, function () {
            return Article::with(['author', 'comments'])
                ->get()
                ->map(function ($article) {
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

        // Test performance N+1 simulé
        if ($request->has('performance_test')) {
            foreach ($articles as $_) {
                usleep(30000);
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

    // Recherche sensible aux accents avec collation utf8mb4_bin
    $articles = Article::whereRaw('title COLLATE utf8mb4_bin LIKE ?', ['%' . $query . '%'])
        ->orWhereRaw('content COLLATE utf8mb4_bin LIKE ?', ['%' . $query . '%'])
        ->get();

    $results = $articles->map(function ($article) {
        return [
            'id' => $article->id,
            'title' => $article->title,
            'content' => substr($article->content, 0, 200),
            'published_at' => $article->published_at,
        ];
    });

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
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
        ]);

        $imagePath = null;

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $filename = Str::uuid() . '.' . $image->getClientOriginalExtension();

            $resizedImage = Image::make($image)
                ->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('webp', 80);

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

        // Supprimer anciennes images
        if ($request->hasFile('image') && $article->image_path) {
            Storage::disk('public')->delete($article->image_path);
        }

        // Nouveau traitement image
        $imagePath = $article->image_path;

        if ($request->hasFile('image')) {
            $image = $request->file('image');

            $filename = Str::uuid() . '.webp';

            $resized = Image::make($image)
                ->resize(1200, null, function ($constraint) {
                    $constraint->aspectRatio();
                    $constraint->upsize();
                })
                ->encode('webp', 80);

            Storage::disk('public')->put("articles/$filename", $resized);

            $imagePath = "articles/$filename";
        }

        $article->update(array_merge($validated, [
            'image_path' => $imagePath,
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

        if ($article->image_path) {
            Storage::disk('public')->delete($article->image_path);
        }

        $article->delete();

        Cache::forget('articles.liste');
        Cache::forget('stats.globales');

        return response()->json(['message' => 'Article deleted successfully']);
    }
}
