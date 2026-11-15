<?php

use App\Http\Controllers\SeoController;
use App\Livewire\Auth\Login;
use App\Livewire\Public\ArticleIndex;
use App\Models\Article;
use App\Models\Category;
use App\Models\ManagementMember;
use App\Models\Media;
use App\Models\OrganizationProfile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $with = ['featuredMedia', 'categories', 'author'];
    $featured = Article::published()->featured()->with($with)->latest('published_at')->first();
    $latest = Article::published()->with($with)
        ->when($featured, fn ($q) => $q->whereKeyNot($featured->id))
        ->latest('published_at')->take(3)->get();
    $categories = Category::withCount(['articles' => fn ($q) => $q->published()])->orderBy('name')->get();
    // Foto yang sama bisa muncul di beberapa berita; ambil lebih banyak lalu unikkan.
    $galleryPhotos = Media::gallery()->take(12)->get()->unique('id')->take(4)->values();

    return view('public.home', [
        'profile' => OrganizationProfile::instance(),
        'featured' => $featured,
        'latest' => $latest,
        'categories' => $categories,
        'galleryPhotos' => $galleryPhotos,
        'stats' => [
            'articles' => Article::published()->count(),
            'categories' => $categories->count(),
            'photos' => Media::gallery()->reorder()->distinct()->count('media.id'),
        ],
    ]);
})->name('home');

Route::get('/profil-organisasi', fn () => view('public.profile', [
    'profile' => OrganizationProfile::instance(),
]))->name('profile');

Route::get('/struktur-kepengurusan', function () {
    $members = ManagementMember::active()->with('photo')->get();

    return view('public.management', [
        'core' => $members->whereNull('division')->values(),
        'divisions' => $members->whereNotNull('division')->groupBy('division'),
    ]);
})->name('management');

Route::get('/berita', ArticleIndex::class)->name('articles.index');
Route::get('/kategori/{category:slug}', ArticleIndex::class)->name('categories.show');

Route::get('/berita/{article:slug}', function (Article $article) {
    abort_unless($article->isPublished(), 404);

    $article->load(['featuredMedia', 'categories', 'author', 'photos']);
    $related = Article::published()->with(['featuredMedia', 'categories', 'author'])
        ->whereKeyNot($article->id)
        ->whereHas('categories', fn ($q) => $q->whereIn('categories.id', $article->categories->pluck('id')))
        ->latest('published_at')->take(3)->get();

    return view('public.articles.show', [
        'article' => $article,
        'related' => $related,
    ]);
})->name('articles.show');

Route::get('/galeri', function () {
    $photos = Media::gallery()->get()->unique('id')->values();

    return view('public.gallery', ['photos' => $photos]);
})->name('gallery');

Route::get('/kontak', fn () => view('public.contact'))->name('contact');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');

Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

Route::post('/logout', function () {
    Auth::guard('web')->logout();
    request()->session()->invalidate();
    request()->session()->regenerateToken();

    return redirect()->route('home');
})->middleware('auth')->name('logout');

require __DIR__.'/cms.php';