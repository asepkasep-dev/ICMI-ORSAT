<?php

namespace App\Livewire\Public;

use App\Models\Article;
use App\Models\Category;
use App\Support\Search;
use Livewire\Attributes\Locked;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $sort = 'newest';

    #[Locked]
    public ?int $categoryId = null;

    public function mount(?Category $category = null): void
    {
        $this->categoryId = $category?->id;
    }

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function resetFilters()
    {
        if ($this->categoryId) {
            return $this->redirectRoute('articles.index');
        }

        $this->reset(['q', 'sort']);
        $this->resetPage();
    }

    public function render()
    {
        $with = ['featuredMedia', 'categories', 'author'];
        $category = $this->categoryId ? Category::find($this->categoryId) : null;

        $featured = ($this->q === '' && ! $category)
            ? Article::published()->featured()->with($with)->latest('published_at')->first()
            : null;

        $query = Article::published()->with($with)
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->id))
            ->when($category, fn ($q) => $q->whereHas('categories', fn ($w) => $w->whereKey($category->id)));

        if ($this->q !== '') {
            Search::apply($query, ['title', 'excerpt', 'content'], $this->q);
        }

        match ($this->sort) {
            'oldest' => $query->orderBy('published_at'),
            'title' => $query->orderBy('title'),
            default => $query->latest('published_at'),
        };

        $page = $this->getPage();
        $pageParams = $page > 1 ? ['page' => $page] : [];

        return view('livewire.public.article-index', [
            'articles' => $query->paginate(9),
            'categories' => Category::withCount(['articles' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'activeCategory' => $category,
            'featured' => $page === 1 ? $featured : null,
        ])->layout('components.layouts.public', [
            'title' => $category ? 'Kategori '.$category->name : 'Berita & Artikel',
            'description' => $category?->description
                ?: ($category ? 'Kumpulan berita kategori '.$category->name.' dari '.config('icmi.name').'.' : 'Berita, kegiatan, dan publikasi resmi '.config('icmi.name').'.'),
            'canonical' => $category
                ? route('categories.show', [$category, ...$pageParams])
                : route('articles.index', $pageParams),
            'robots' => ($this->q !== '' || $this->sort !== 'newest') ? 'noindex, follow' : null,
        ]);
    }
}
