<?php

namespace App\Http\Controllers;

use App\Models\Article;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * XML sitemap covering the articles index, topic pages, and every
     * published article so search engines can crawl the whole section.
     */
    public function index(): Response
    {
        $articles = Article::published()
            ->orderByDesc('published_at')
            ->get();

        $topics = $articles
            ->pluck('category')
            ->filter()
            ->unique()
            ->values();

        $urls = [];

        $urls[] = [
            'loc' => route('home'),
            'changefreq' => 'weekly',
            'priority' => '1.0',
        ];

        $urls[] = [
            'loc' => route('articles.index'),
            'changefreq' => 'daily',
            'priority' => '0.9',
        ];

        // Industry landing pages.
        foreach ([
            'industries.junior-mining',
            'industries.manufacturing',
            'industries.tourism-councils',
            'industries.municipalities',
        ] as $industry) {
            $urls[] = [
                'loc' => route($industry),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        // Company / legal pages.
        foreach (['about', 'contact', 'privacy'] as $page) {
            $urls[] = [
                'loc' => route($page),
                'changefreq' => 'yearly',
                'priority' => '0.4',
            ];
        }

        foreach ($topics as $topic) {
            $urls[] = [
                'loc' => route('articles.topic', $topic),
                'changefreq' => 'weekly',
                'priority' => '0.6',
            ];
        }

        foreach ($articles as $article) {
            $urls[] = [
                'loc' => route('articles.show', $article),
                'lastmod' => $article->updated_at?->toAtomString(),
                'changefreq' => 'monthly',
                'priority' => '0.8',
            ];
        }

        return response()
            ->view('articles.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }

    /**
     * robots.txt that allows crawling and points at the sitemap.
     */
    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Disallow: /manage',
            'Disallow: /dashboard',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n")
            ->header('Content-Type', 'text/plain');
    }
}
