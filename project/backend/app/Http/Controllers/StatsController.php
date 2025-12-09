<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Comment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class StatsController extends Controller
{
    public function index()
    {
<<<<<<< HEAD
        // ⏳ Cache 5 minutes
        return Cache::remember('stats.globales', 300, function () {
=======
        // PERF-003 : commit personnel pour cache API
        $totalArticles = Article::count();
        $totalComments = Comment::count();
        $totalUsers = User::count();
>>>>>>> main

            $totalArticles = Article::count();
            $totalComments = Comment::count();
            $totalUsers = User::count();

            $mostCommented = Article::select('articles.*', DB::raw('COUNT(comments.id) as comments_count'))
                ->leftJoin('comments', 'articles.id', '=', 'comments.article_id')
                ->groupBy(
                    'articles.id', 'articles.title', 'articles.content', 'articles.author_id',
                    'articles.image_path', 'articles.published_at', 'articles.created_at', 'articles.updated_at'
                )
                ->orderBy('comments_count', 'desc')
                ->limit(5)
                ->get();

            $recentArticles = Article::with('author')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get();

            return [
                'total_articles' => $totalArticles,
                'total_comments' => $totalComments,
                'total_users' => $totalUsers,
                'most_commented' => $mostCommented->map(fn($article) => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'comments_count' => $article->comments_count,
                ]),
                'recent_articles' => $recentArticles->map(fn($article) => [
                    'id' => $article->id,
                    'title' => $article->title,
                    'author' => $article->author->name,
                    'created_at' => $article->created_at,
                ]),
            ];
        });
    }
}

