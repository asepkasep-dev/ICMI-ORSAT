<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrganizationProfile extends Model
{
    protected $fillable = [
        'history', 'vision', 'mission', 'goals',
        'chairman_message', 'chairman_name', 'chairman_photo_media_id',
    ];

    public function chairmanPhoto(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'chairman_photo_media_id');
    }

    public static function instance(): self
    {
        return static::first() ?? new static();
    }

    public function missionItems(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $this->mission))));
    }

    public function goalItems(): array
    {
        return array_values(array_filter(array_map('trim', explode("\n", (string) $this->goals))));
    }
}