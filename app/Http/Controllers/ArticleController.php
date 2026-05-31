<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Services\ArticleContent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ArticleController extends Controller
{
    /**
     * Public index of all published articles, newest first.
     */
    public function index()
    {
        $articles = Article::published()
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        $categories = $articles
            ->pluck('category')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        return view('articles.index', [
            'articles' => $articles,
            'categories' => $categories,
        ]);
    }

    /**
     * Articles filtered to a single topic (category).
     */
    public function topic(string $topic)
    {
        $articles = Article::published()
            ->where('category', $topic)
            ->orderByDesc('published_at')
            ->orderByDesc('created_at')
            ->get();

        abort_if($articles->isEmpty(), 404);

        return view('articles.topic', [
            'topic' => $topic,
            'articles' => $articles,
        ]);
    }

    /**
     * A single published article, with its H-tag table of contents.
     */
    public function show(Article $article, Request $request, ArticleContent $content)
    {
        abort_unless(
            $article->status === 'published'
                && (is_null($article->published_at) || $article->published_at->isPast()),
            404
        );

        $rendered = $content->render($article->body_md);

        $this->recordView($article, $request);

        $related = Article::published()
            ->whereKeyNot($article->getKey())
            ->when($article->category, fn ($q) => $q->where('category', $article->category))
            ->orderByDesc('published_at')
            ->limit(3)
            ->get();

        return view('articles.show', [
            'article' => $article,
            'html' => $rendered['html'],
            'toc' => $rendered['toc'],
            'related' => $related,
        ]);
    }

    /**
     * Count a human pageview, skipping known crawlers/unfurlers.
     * Mirrors the bot filter used for one-pager view tracking.
     */
    protected function recordView(Article $article, Request $request): void
    {
        $userAgent = $request->userAgent() ?? '';
        $botPatterns = [
            'bot', 'crawler', 'spider', 'slack', 'linkedin', 'telegram',
            'whatsapp', 'facebook', 'twitter', 'discord', 'preview',
        ];

        foreach ($botPatterns as $pattern) {
            if (Str::contains($userAgent, $pattern, ignoreCase: true)) {
                return;
            }
        }

        $article->increment('view_count');
    }
}
