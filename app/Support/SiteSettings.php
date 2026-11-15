<?php

namespace App\Support;

use App\Models\Media;
use App\Models\Setting;

class SiteSettings
{
    // Kunci tabel settings = kunci config('icmi.*') yang ditimpa; nilai kosong memakai bawaan config.
    public const FIELDS = [
        'name', 'short_name', 'long_name', 'tagline', 'term', 'description',
        'contact.address', 'contact.email', 'contact.phone',
        'social.instagram', 'social.facebook', 'social.youtube', 'social.tiktok',
    ];

    public const LOGO = 'logo_media_id';

    public static function apply(): void
    {
        $stored = static::stored();

        foreach (self::FIELDS as $key) {
            if (filled($stored[$key] ?? null)) {
                config(['icmi.'.$key => $stored[$key]]);
            }
        }

        config(['icmi.logo_url' => static::logo($stored)?->url()]);
    }

    /** @return array<string, ?string> */
    public static function values(): array
    {
        $stored = static::stored();

        return collect(self::FIELDS)
            ->mapWithKeys(fn (string $key) => [$key => filled($stored[$key] ?? null) ? $stored[$key] : config('icmi.'.$key)])
            ->all();
    }

    public static function logo(?array $stored = null): ?Media
    {
        $id = ($stored ?? static::stored())[self::LOGO] ?? null;

        return filled($id) ? Media::find($id) : null;
    }

    private static function stored(): array
    {
        return Setting::whereIn('key', [...self::FIELDS, self::LOGO])->pluck('value', 'key')->all();
    }
}
