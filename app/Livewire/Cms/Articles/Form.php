<?php

namespace App\Livewire\Cms\Articles;

use App\Enums\ArticleStatus;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Support\ArticleWorkflow;
use App\Support\ContentSanitizer;
use App\Support\MediaStorage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?int $articleId = null;

    public string $title = '';
    public string $slug = '';
    public bool $slugTouched = false;
    public string $excerpt = '';
    public string $content = '';
    public array $categories = [];
    public bool $is_featured = false;
    public string $seo_title = '';
    public string $seo_description = '';
    public ?int $featuredMediaId = null;
    public bool $removeFeatured = false;

    public $newFeatured = null;
    public array $newPhotos = [];

    public string $scheduledAt = '';
    public string $revisionNote = '';

    public bool $mediaPickerOpen = false;
    public string $mediaSearch = '';

    public function mount(?Article $article = null): void
    {
        if (! $article || ! $article->exists) {
            $this->authorize('create', Article::class);
            $this->scheduledAt = now()->addHour()->format('Y-m-d\TH:i');

            return;
        }

        $this->authorize('update', $article);

        $this->articleId = $article->id;
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->slugTouched = true;
        $this->excerpt = (string) $article->excerpt;
        $this->content = $this->toEditorHtml($article->content);
        $this->categories = $article->categories->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->is_featured = $article->is_featured;
        $this->seo_title = (string) $article->seo_title;
        $this->seo_description = (string) $article->seo_description;
        $this->featuredMediaId = $article->featured_media_id;
        $this->revisionNote = (string) $article->revision_note;
        $this->scheduledAt = ($article->published_at ?? now()->addHour())->format('Y-m-d\TH:i');
    }

    private function toEditorHtml(string $content): string
    {
        if (preg_match('/<[a-z][\s\S]*?>/i', $content)) {
            return $content;
        }

        return collect(preg_split('/\n\s*\n/', $content))
            ->map(fn ($p) => trim($p))
            ->filter()
            ->map(fn ($p) => '<p>'.e($p).'</p>')
            ->implode('');
    }

    public function updatedTitle(string $value): void
    {
        if (! $this->slugTouched) {
            $this->slug = Str::slug($value);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugTouched = true;
    }

    public function updatedNewFeatured(): void
    {
        $this->removeFeatured = false;
    }

    public function removeFeatured(): void
    {
        $this->featuredMediaId = null;
        $this->removeFeatured = true;
        $this->newFeatured = null;
    }

    public function openMediaPicker(): void
    {
        $this->authorize('viewAny', Media::class);
        $this->mediaPickerOpen = true;
        $this->mediaSearch = '';
    }

    public function closeMediaPicker(): void
    {
        $this->mediaPickerOpen = false;
    }

    public function selectMedia(int $id): void
    {
        $media = Media::findOrFail($id);
        $this->authorize('view', $media);

        $this->featuredMediaId = $media->id;
        $this->removeFeatured = false;
        $this->newFeatured = null;
        $this->mediaPickerOpen = false;
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', 'alpha_dash', function (string $attribute, mixed $value, \Closure $fail) {
                if ($value === '' || $value === null) {
                    return;
                }
                $exists = Article::withTrashed()->where('slug', $value)
                    ->when($this->articleId, fn ($q) => $q->whereKeyNot($this->articleId))
                    ->exists();
                if ($exists) {
                    $fail('Slug sudah digunakan berita lain.');
                }
            }],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', function (string $attribute, mixed $value, \Closure $fail) {
                if (mb_strlen(ContentSanitizer::text($value)) < 20) {
                    $fail('Isi berita minimal 20 karakter.');
                }
            }],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'is_featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:200'],
            'seo_description' => ['nullable', 'string', 'max:300'],
            'featuredMediaId' => ['nullable', 'integer', 'exists:media,id'],
            'newFeatured' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'newPhotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    protected function persist(): Article
    {
        $this->validate();

        $user = auth()->user();
        $article = $this->articleId ? Article::findOrFail($this->articleId) : new Article();
        $this->authorize($this->articleId ? 'update' : 'create', $article);

        $slug = $this->slug !== '' ? $this->slug : Article::uniqueSlug($this->title, $this->articleId);

        $featuredMediaId = $this->removeFeatured ? null : $this->featuredMediaId;
        if ($this->newFeatured) {
            $featuredMediaId = MediaStorage::store($this->newFeatured)->id;
        }

        $article->fill([
            'user_id' => $article->user_id ?? $user->id,
            'title' => $this->title,
            'slug' => $slug,
            'excerpt' => $this->excerpt ?: null,
            'content' => ContentSanitizer::clean($this->content),
            // Writer tidak mengelola unggulan: pertahankan nilai yang ditetapkan Admin.
            'is_featured' => $user->isAdmin() ? $this->is_featured : ($article->is_featured ?? false),
            'seo_title' => $this->seo_title ?: null,
            'seo_description' => $this->seo_description ?: null,
            'featured_media_id' => $featuredMediaId,
        ]);

        if (! $article->exists) {
            $article->status = ArticleStatus::Draft;
        }

        $article->save();
        $this->articleId = $article->id;
        $article->categories()->sync($this->categories);

        $order = $article->photos()->count();
        foreach ($this->newPhotos as $photo) {
            $media = MediaStorage::store($photo);
            $article->photos()->attach($media->id, ['show_in_gallery' => true, 'sort_order' => $order++]);
        }
        $this->newPhotos = [];
        $this->newFeatured = null;

        ActivityLog::record(
            $article->wasRecentlyCreated ? 'article.created' : 'article.updated',
            $article,
            ($article->wasRecentlyCreated ? 'Membuat' : 'Memperbarui').' berita "'.$article->title.'"'
        );

        return $article;
    }

    public function save()
    {
        $isNew = ! $this->articleId;
        $article = $this->persist();

        session()->flash('status', $isNew ? 'Berita tersimpan sebagai draf.' : 'Perubahan berita tersimpan.');

        return $this->redirectRoute('cms.articles');
    }

    public function submitReview()
    {
        $article = $this->persist();
        $this->authorize('submitReview', $article);

        ArticleWorkflow::submitReview($article);
        session()->flash('status', 'Berita "'.$article->title.'" diajukan untuk review Admin.');

        return $this->redirectRoute('cms.articles');
    }

    public function publish()
    {
        $article = $this->persist();
        $this->authorize('publish', $article);

        if ($article->status === ArticleStatus::Archived) {
            session()->flash('error', 'Berita diarsipkan harus dipulihkan terlebih dahulu sebelum diterbitkan.');

            return $this->redirectRoute('cms.articles');
        }

        ArticleWorkflow::publish($article);
        session()->flash('status', 'Berita "'.$article->title.'" berhasil diterbitkan.');

        return $this->redirectRoute('cms.articles');
    }

    public function schedule()
    {
        $article = $this->persist();
        $this->authorize('schedule', $article);

        $this->validate([
            'scheduledAt' => ['required', 'date', 'after:now'],
        ], [], ['scheduledAt' => 'waktu publikasi']);

        if ($article->status === ArticleStatus::Archived) {
            session()->flash('error', 'Berita diarsipkan harus dipulihkan terlebih dahulu sebelum dijadwalkan.');

            return $this->redirectRoute('cms.articles');
        }

        ArticleWorkflow::schedule($article, Carbon::parse($this->scheduledAt));
        session()->flash('status', 'Berita "'.$article->title.'" dijadwalkan terbit pada '.Carbon::parse($this->scheduledAt)->translatedFormat('d M Y H:i').'.');

        return $this->redirectRoute('cms.articles');
    }

    public function requestRevision()
    {
        $article = $this->persist();
        $this->authorize('review', $article);

        $this->validate([
            'revisionNote' => ['required', 'string', 'min:5', 'max:1000'],
        ], [], ['revisionNote' => 'catatan revisi']);

        ArticleWorkflow::requestRevision($article, $this->revisionNote);
        session()->flash('status', 'Berita "'.$article->title.'" dikembalikan untuk revisi.');

        return $this->redirectRoute('cms.articles');
    }

    public function render()
    {
        $user = auth()->user();

        return view('livewire.cms.articles.form', [
            'categoryOptions' => Category::orderBy('name')->get(),
            'featuredMedia' => $this->featuredMediaId ? Media::find($this->featuredMediaId) : null,
            'article' => $this->articleId ? Article::find($this->articleId) : null,
            'mediaOptions' => $this->mediaPickerOpen
                ? Media::accessibleBy($user)
                    ->when($this->mediaSearch !== '', fn ($q) => $q->where(fn ($w) => $w
                        ->where('file_name', 'like', '%'.$this->mediaSearch.'%')
                        ->orWhere('alt', 'like', '%'.$this->mediaSearch.'%')))
                    ->latest()->limit(24)->get()
                : collect(),
        ])->layout('components.layouts.cms', [
            'title' => $this->articleId ? 'Edit Berita' : 'Tulis Berita',
        ]);
    }
}