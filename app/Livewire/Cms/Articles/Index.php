<?php

namespace App\Livewire\Cms\Articles;

use App\Enums\ArticleStatus;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Category;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    #[Url]
    public string $q = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $category = '';

    public ?int $confirmId = null;

    public string $confirmTitle = '';

    public function updatedQ(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedCategory(): void
    {
        $this->resetPage();
    }

    public function confirmDelete(int $id): void
    {
        $article = Article::findOrFail($id);
        $this->authorize('delete', $article);

        $this->confirmId = $article->id;
        $this->confirmTitle = $article->title;
    }

    public function deleteConfirmed(): void
    {
        $article = Article::findOrFail($this->confirmId);
        $this->authorize('delete', $article);

        $article->delete();
        ActivityLog::record('article.deleted', $article, 'Menghapus berita "'.$article->title.'"');

        $this->confirmId = null;
        $this->confirmTitle = '';
        session()->flash('status', 'Berita "'.$article->title.'" dihapus.');
        $this->resetPage();
    }

    public function render()
    {
        $user = auth()->user();
        $query = Article::with(['author', 'categories', 'featuredMedia']);

        if ($user->isWriter()) {
            $query->where('user_id', $user->id);
        }

        if ($this->q !== '') {
            $term = '%'.$this->q.'%';
            $query->where(fn ($w) => $w->where('title', 'like', $term)->orWhere('slug', 'like', $term));
        }

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->category !== '') {
            $query->whereHas('categories', fn ($w) => $w->where('categories.slug', $this->category));
        }

        return view('livewire.cms.articles.index', [
            'articles' => $query->latest('updated_at')->paginate(10),
            'categories' => Category::orderBy('name')->get(),
            'statuses' => ArticleStatus::cases(),
        ])->layout('components.layouts.cms', ['title' => 'Manajemen Berita']);
    }
}