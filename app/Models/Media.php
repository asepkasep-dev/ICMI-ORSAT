<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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

    public function url(): string
    {
        return Storage::disk($this->disk)->url($this->file_path);
    }

    public function scopeGallery(Builder $query): Builder
    {
        return $query->select('media.*', 'articles.title as article_title', 'articles.slug as article_slug')
            ->join('article_media', 'article_media.media_id', '=', 'media.id')
            ->join('articles', 'articles.id', '=', 'article_media.article_id')
            ->where('article_media.show_in_gallery', true)
            ->where('articles.status', 'published')
            ->whereNotNull('articles.published_at')
            ->where('articles.published_at', '<=', now())
            ->whereNull('articles.deleted_at')
            ->orderByDesc('articles.published_at')
            ->orderBy('article_media.sort_order');
    }
}