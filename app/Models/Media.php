<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Media extends Model
{
    protected $table = 'media';

    protected $fillable = [
        'user_id', 'file_name', 'file_path', 'disk', 'mime_type',
        'file_size', 'width', 'height', 'alt', 'caption',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function articles(): BelongsToMany
    {
        return $this->belongsToMany(Article::class)->withPivot(['show_in_gallery', 'sort_order']);
    }

    public function featuredIn(): HasMany
    {
        return $this->hasMany(Article::class, 'featured_media_id');
    }

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function usageCount(): int
    {
        return $this->articles()->withTrashed()->count()
            + $this->featuredIn()->withTrashed()->count()
            + OrganizationProfile::where('chairman_photo_media_id', $this->id)->count();
    }

    public function scopeAccessibleBy(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        return $query->where(fn (Builder $q) => $q
            ->where('media.user_id', $user->id)
            ->orWhereHas('articles', fn (Builder $a) => $a->withTrashed()->where('articles.user_id', $user->id))
            ->orWhereHas('featuredIn', fn (Builder $a) => $a->withTrashed()->where('articles.user_id', $user->id)));
    }

    public function scopeGallery(Builder $query): Builder
    {
        return $query->select('media.*', 'articles.title as article_title', 'articles.slug as article_slug')
            ->join('article_media', 'article_media.media_id', '=', 'media.id')
            ->join('articles', 'articles.id', '=', 'article_media.article_id')
            ->where('article_media.show_in_gallery', true)
            ->whereIn('articles.status', ['published', 'scheduled'])
            ->whereNotNull('articles.published_at')
            ->where('articles.published_at', '<=', now())
            ->whereNull('articles.deleted_at')
            ->orderByDesc('articles.published_at')
            ->orderBy('article_media.sort_order');
    }
}