<?php

namespace App\Models;

use App\Enums\ArticleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Article extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'title', 'slug', 'excerpt', 'content', 'status', 'revision_note',
        'published_at', 'is_featured', 'seo_title', 'seo_description', 'featured_media_id',
    ];

    protected function casts(): array
    {
        return [
            'status' => ArticleStatus::class,
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function photos(): BelongsToMany
    {
        return $this->belongsToMany(Media::class)
            ->withPivot(['show_in_gallery', 'sort_order'])
            ->orderByPivot('sort_order');
    }

    public function featuredMedia(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'featured_media_id');
    }

    public function metaDescription(): string
    {
        $text = $this->seo_description ?: $this->excerpt ?: strip_tags(str_replace('<', ' <', (string) $this->content));

        return Str::limit(trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5))), 160);
    }

    public function scopePublished(Builder $query): Builder
    {
        // Artikel terjadwal yang sudah lewat waktu tayang tetap tampil walau cron belum berjalan.
        return $query->where(function (Builder $q) {
            $q->where('status', ArticleStatus::Published)
                ->orWhere(fn (Builder $s) => $s->where('status', ArticleStatus::Scheduled));
        })
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function isPublished(): bool
    {
        return in_array($this->status, [ArticleStatus::Published, ArticleStatus::Scheduled], true)
            && $this->published_at !== null
            && $this->published_at->lte(now());
    }

    public function readingTime(): int
    {
        return max(1, (int) ceil(str_word_count(strip_tags($this->content)) / 200));
    }

    public static function uniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $base = Str::slug($title) ?: 'berita';
        $slug = $base;
        $i = 2;

        while (static::withTrashed()
            ->where('slug', $slug)
            ->when($ignoreId, fn (Builder $q) => $q->whereKeyNot($ignoreId))
            ->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}