<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\Category;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

class SeoController extends Controller
{
    public function sitemap(): Response
    {
        $articles = Article::published()->latest('published_at')->get(['id', 'slug', 'published_at', 'updated_at']);
        $lastmod = fn (Article $a) => $a->updated_at->max($a->published_at);
        $latest = $articles->map($lastmod)->max();

        $categories = Category::query()
            ->whereHas('articles', fn ($q) => $q->published())
            ->withMax(['articles' => fn ($q) => $q->published()], 'published_at')
            ->orderBy('name')
            ->get();

        $urls = collect([
            ['loc' => route('home'), 'lastmod' => $latest],
            ['loc' => route('profile')],
            ['loc' => route('management')],
            ['loc' => route('articles.index'), 'lastmod' => $latest],
            ['loc' => route('gallery')],
            ['loc' => route('contact')],
        ])
            ->concat($categories->map(fn (Category $c) => [
                'loc' => route('categories.show', $c),
                'lastmod' => $c->articles_max_published_at ? Carbon::parse($c->articles_max_published_at) : null,
            ]))
            ->concat($articles->map(fn (Article $a) => [
                'loc' => route('articles.show', $a),
                'lastmod' => $lastmod($a),
            ]));

        return response()
            ->view('seo.sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $lines = [
            'User-agent: *',
            'Disallow: /cms',
            'Disallow: /login',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n")->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
