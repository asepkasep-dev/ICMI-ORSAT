<?php

namespace App\Livewire\Cms\Articles;

use App\Enums\ArticleStatus;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Category;
use App\Support\ArticleWorkflow;
use Illuminate\Support\Carbon;
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

    public ?int $scheduleId = null;
    public string $scheduleTitle = '';
    public string $scheduleAt = '';

    public ?int $revisionId = null;
    public string $revisionTitle = '';
    public string $revisionNote = '';

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
        $this->dispatch('toast', message: 'Berita "'.$article->title.'" dihapus.');
        $this->resetPage();
    }

    public function publish(int $id): void
    {
        $article = Article::findOrFail($id);
        $this->authorize('publish', $article);

        if ($article->status === ArticleStatus::Archived) {
            $this->dispatch('toast', type: 'error', message: 'Berita diarsipkan harus dipulihkan terlebih dahulu.');

            return;
        }

        ArticleWorkflow::publish($article);
        $this->dispatch('toast', message: 'Berita "'.$article->title.'" berhasil diterbitkan.');
    }

    public function submitReview(int $id): void
    {
        $article = Article::findOrFail($id);
        $this->authorize('submitReview', $article);

        ArticleWorkflow::submitReview($article);
        $this->dispatch('toast', message: 'Berita "'.$article->title.'" diajukan untuk review Admin.');
    }

    public function openSchedule(int $id): void
    {
        $article = Article::findOrFail($id);
        $this->authorize('schedule', $article);

        $this->scheduleId = $article->id;
        $this->scheduleTitle = $article->title;
        $this->scheduleAt = ($article->status === ArticleStatus::Scheduled && $article->published_at
            ? $article->published_at
            : now()->addHour())->format('Y-m-d\TH:i');
    }

    public function scheduleConfirmed(): void
    {
        $article = Article::findOrFail($this->scheduleId);
        $this->authorize('schedule', $article);

        $this->validate([
            'scheduleAt' => ['required', 'date', 'after:now'],
        ], [], ['scheduleAt' => 'waktu publikasi']);

        $at = Carbon::parse($this->scheduleAt);
        ArticleWorkflow::schedule($article, $at);

        $this->reset(['scheduleId', 'scheduleTitle', 'scheduleAt']);
        $this->dispatch('toast', message: 'Berita "'.$article->title.'" dijadwalkan terbit pada '.$at->translatedFormat('d M Y H:i').'.');
    }

    public function openRevision(int $id): void
    {
        $article = Article::findOrFail($id);
        $this->authorize('review', $article);

        $this->revisionId = $article->id;
        $this->revisionTitle = $article->title;
        $this->revisionNote = (string) $article->revision_note;
    }

    public function revisionConfirmed(): void
    {
        $article = Article::findOrFail($this->revisionId);
        $this->authorize('review', $article);

        $this->validate([
            'revisionNote' => ['required', 'string', 'min:5', 'max:1000'],
        ], [], ['revisionNote' => 'catatan revisi']);

        ArticleWorkflow::requestRevision($article, $this->revisionNote);

        $this->reset(['revisionId', 'revisionTitle', 'revisionNote']);
        $this->dispatch('toast', message: 'Berita "'.$article->title.'" dikembalikan untuk revisi.');
    }

    public function archive(int $id): void
    {
        $article = Article::findOrFail($id);
        $this->authorize('archive', $article);

        ArticleWorkflow::archive($article);
        $this->dispatch('toast', message: 'Berita "'.$article->title.'" diarsipkan dan tidak tampil lagi di website.');
    }

    public function restore(int $id): void
    {
        $article = Article::findOrFail($id);
        $this->authorize('restore', $article);

        ArticleWorkflow::restore($article);
        $this->dispatch('toast', message: 'Berita "'.$article->title.'" dipulihkan ke draf.');
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