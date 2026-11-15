<?php

namespace App\Livewire\Cms\Articles;

use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;
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

    public function mount(?Article $article = null): void
    {
        if (! $article || ! $article->exists) {
            $this->authorize('create', Article::class);

            return;
        }

        $this->authorize('update', $article);

        $this->articleId = $article->id;
        $this->title = $article->title;
        $this->slug = $article->slug;
        $this->slugTouched = true;
        $this->excerpt = (string) $article->excerpt;
        $this->content = $article->content;
        $this->categories = $article->categories->pluck('id')->map(fn ($id) => (string) $id)->all();
        $this->is_featured = $article->is_featured;
        $this->seo_title = (string) $article->seo_title;
        $this->seo_description = (string) $article->seo_description;
        $this->featuredMediaId = $article->featured_media_id;
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

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:5', 'max:200'],
            'slug' => ['nullable', 'string', 'max:220', 'alpha_dash'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string', 'min:20'],
            'categories' => ['required', 'array', 'min:1'],
            'categories.*' => ['integer', 'exists:categories,id'],
            'is_featured' => ['boolean'],
            'seo_title' => ['nullable', 'string', 'max:200'],
            'seo_description' => ['nullable', 'string', 'max:300'],
            'newFeatured' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'newPhotos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function save()
    {
        $this->validate();

        $user = auth()->user();
        $article = $this->articleId ? Article::findOrFail($this->articleId) : new Article();
        $this->authorize($this->articleId ? 'update' : 'create', $article);

        if ($this->slug !== '') {
            $exists = Article::withTrashed()->where('slug', $this->slug)
                ->when($this->articleId, fn ($q) => $q->whereKeyNot($this->articleId))
                ->exists();
            if ($exists) {
                $this->addError('slug', 'Slug sudah digunakan berita lain.');

                return;
            }
            $slug = $this->slug;
        } else {
            $slug = Article::uniqueSlug($this->title, $this->articleId);
        }

        $featuredMediaId = $this->removeFeatured ? null : $this->featuredMediaId;
        if ($this->newFeatured) {
            $featuredMediaId = $this->storeMedia($this->newFeatured)->id;
        }

        $article->fill([
            'user_id' => $article->user_id ?? $user->id,
            'title' => $this->title,
            'slug' => $slug,
            'excerpt' => $this->excerpt ?: null,
            'content' => $this->content,
            'is_featured' => $user->isAdmin() ? $this->is_featured : false,
            'seo_title' => $this->seo_title ?: null,
            'seo_description' => $this->seo_description ?: null,
            'featured_media_id' => $featuredMediaId,
        ]);

        if (! $article->exists) {
            $article->status = \App\Enums\ArticleStatus::Draft;
        }

        $article->save();
        $article->categories()->sync($this->categories);

        $order = $article->photos()->count();
        foreach ($this->newPhotos as $photo) {
            $media = $this->storeMedia($photo);
            $article->photos()->attach($media->id, ['show_in_gallery' => true, 'sort_order' => $order++]);
        }

        ActivityLog::record(
            $this->articleId ? 'article.updated' : 'article.created',
            $article,
            ($this->articleId ? 'Memperbarui' : 'Membuat').' berita "'.$article->title.'"'
        );

        session()->flash('status', $this->articleId ? 'Perubahan berita tersimpan.' : 'Berita tersimpan sebagai draf.');

        return $this->redirectRoute('cms.articles');
    }

    protected function storeMedia($upload): Media
    {
        $path = $upload->store('media', 'public');
        [$width, $height] = getimagesize(Storage::disk('public')->path($path));

        return Media::create([
            'user_id' => auth()->id(),
            'file_name' => $upload->getClientOriginalName(),
            'file_path' => $path,
            'disk' => 'public',
            'mime_type' => $upload->getMimeType(),
            'file_size' => $upload->getSize(),
            'width' => $width,
            'height' => $height,
            'alt' => pathinfo($upload->getClientOriginalName(), PATHINFO_FILENAME),
        ]);
    }

    public function render()
    {
        return view('livewire.cms.articles.form', [
            'categoryOptions' => Category::orderBy('name')->get(),
            'featuredMedia' => $this->featuredMediaId ? Media::find($this->featuredMediaId) : null,
            'article' => $this->articleId ? Article::find($this->articleId) : null,
        ])->layout('components.layouts.cms', [
            'title' => $this->articleId ? 'Edit Berita' : 'Tulis Berita',
        ]);
    }
}