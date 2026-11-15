<?php

namespace App\Livewire\Cms\Categories;

use App\Models\ActivityLog;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Url;
use Livewire\Component;

class Index extends Component
{
    #[Url]
    public string $q = '';

    public bool $formOpen = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $slug = '';
    public bool $slugTouched = false;
    public string $description = '';

    public ?int $deleteId = null;
    public string $deleteName = '';
    public int $deleteCount = 0;
    public string $moveTo = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Category::class);
    }

    public function updatedName(): void
    {
        if (! $this->slugTouched) {
            $this->slug = Str::slug($this->name);
        }
    }

    public function updatedSlug(): void
    {
        $this->slugTouched = true;
    }

    public function openCreate(): void
    {
        $this->authorize('create', Category::class);

        $this->reset(['editingId', 'name', 'slug', 'slugTouched', 'description']);
        $this->resetValidation();
        $this->formOpen = true;
    }

    public function openEdit(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('update', $category);

        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->slugTouched = true;
        $this->description = (string) $category->description;
        $this->resetValidation();
        $this->formOpen = true;
    }

    public function closeForm(): void
    {
        $this->formOpen = false;
        $this->resetValidation();
    }

    public function save(): void
    {
        $category = $this->editingId ? Category::findOrFail($this->editingId) : null;
        $this->authorize($category ? 'update' : 'create', $category ?? Category::class);

        $this->slug = Str::slug($this->slug !== '' ? $this->slug : $this->name);

        $data = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:60', Rule::unique('categories', 'name')->ignore($this->editingId)],
            'slug' => ['required', 'string', 'max:80', Rule::unique('categories', 'slug')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:255'],
        ], [], [
            'name' => 'nama kategori',
            'slug' => 'slug',
            'description' => 'deskripsi',
        ]);

        if ($category) {
            $category->update($data);
            ActivityLog::record('category.updated', $category, 'Memperbarui kategori "'.$category->name.'"');
            $this->dispatch('toast', message: 'Kategori "'.$category->name.'" diperbarui.');
        } else {
            $category = Category::create($data);
            ActivityLog::record('category.created', $category, 'Membuat kategori "'.$category->name.'"');
            $this->dispatch('toast', message: 'Kategori "'.$category->name.'" dibuat.');
        }

        $this->formOpen = false;
        $this->reset(['editingId', 'name', 'slug', 'slugTouched', 'description']);
    }

    public function confirmDelete(int $id): void
    {
        $category = Category::findOrFail($id);
        $this->authorize('delete', $category);

        $this->deleteId = $category->id;
        $this->deleteName = $category->name;
        $this->deleteCount = DB::table('article_category')->where('category_id', $category->id)->count();
        $this->moveTo = '';
        $this->resetValidation();
    }

    public function deleteConfirmed(): void
    {
        $category = Category::findOrFail($this->deleteId);
        $this->authorize('delete', $category);

        $count = DB::table('article_category')->where('category_id', $category->id)->count();

        if ($count > 0) {
            $this->validate([
                'moveTo' => ['required', 'integer', Rule::exists('categories', 'id')->where(fn ($query) => $query->where('id', '!=', $category->id))],
            ], [], ['moveTo' => 'kategori tujuan']);

            $target = Category::findOrFail($this->moveTo);

            DB::transaction(function () use ($category, $target) {
                $articleIds = DB::table('article_category')->where('category_id', $category->id)->pluck('article_id');

                foreach ($articleIds as $articleId) {
                    DB::table('article_category')->insertOrIgnore([
                        'article_id' => $articleId,
                        'category_id' => $target->id,
                    ]);
                }

                $category->delete();
            });

            ActivityLog::record('category.deleted', $category, 'Menghapus kategori "'.$category->name.'"; '.$count.' berita dipindahkan ke "'.$target->name.'"');
            $this->dispatch('toast', message: 'Kategori "'.$category->name.'" dihapus; '.$count.' berita dipindahkan ke "'.$target->name.'".');
        } else {
            $category->delete();
            ActivityLog::record('category.deleted', $category, 'Menghapus kategori "'.$category->name.'"');
            $this->dispatch('toast', message: 'Kategori "'.$category->name.'" dihapus.');
        }

        $this->reset(['deleteId', 'deleteName', 'deleteCount', 'moveTo']);
    }

    public function render()
    {
        $query = Category::withCount('articles')->orderBy('name');

        if ($this->q !== '') {
            $term = '%'.$this->q.'%';
            $query->where(fn ($w) => $w->where('name', 'like', $term)->orWhere('slug', 'like', $term));
        }

        return view('livewire.cms.categories.index', [
            'categories' => $query->get(),
            'moveOptions' => Category::where('id', '!=', $this->deleteId ?? 0)->orderBy('name')->get(),
        ])->layout('components.layouts.cms', ['title' => 'Kategori Berita']);
    }
}