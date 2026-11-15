<?php

namespace App\Livewire\Public;

use App\Models\Article;
use App\Models\Category;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class ArticleIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $category = '';

    #[Url]
    public string $sort = 'newest';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function updatedSort(): void
    {
        $this->resetPage();
    }

    public function setCategory(string $slug): void
    {
        $this->category = $slug;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['q', 'category', 'sort']);
        $this->resetPage();
    }

    public function render()
    {
        $with = ['featuredMedia', 'categories', 'author'];

        $featured = ($this->q === '' && $this->category === '')
            ? Article::published()->featured()->with($with)->latest('published_at')->first()
            : null;

        $query = Article::published()->with($with)
            ->when($featured, fn ($q) => $q->whereKeyNot($featured->id));

        if ($this->q !== '') {
            $term = '%'.$this->q.'%';
            $query->where(fn ($w) => $w->where('title', 'like', $term)
                ->orWhere('excerpt', 'like', $term)
                ->orWhere('content', 'like', $term));
        }

        if ($this->category !== '') {
            $query->whereHas('categories', fn ($w) => $w->where('categories.slug', $this->category));
        }

        match ($this->sort) {
            'oldest' => $query->orderBy('published_at'),
            'title' => $query->orderBy('title'),
            default => $query->latest('published_at'),
        };

        return view('livewire.public.article-index', [
            'articles' => $query->paginate(9),
            'categories' => Category::withCount(['articles' => fn ($q) => $q->published()])->orderBy('name')->get(),
            'featured' => $this->getPage() === 1 ? $featured : null,
        ])->layout('components.layouts.public', [
            'title' => 'Berita & Artikel',
            'description' => 'Berita, kegiatan, dan publikasi resmi ICMI Kecamatan Caringin.',
        ]);
    }
}