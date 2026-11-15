<?php

namespace App\Support;

use HTMLPurifier;
use HTMLPurifier_Config;

class ContentSanitizer
{
    private static ?HTMLPurifier $purifier = null;

    public static function clean(?string $html): string
    {
        $html = trim((string) $html);

        if ($html === '') {
            return '';
        }

        // h1 milik judul halaman; heading dari Trix (h1) diturunkan agar hanya ada satu h1 per halaman.
        $html = preg_replace('~<(/?)h1(?=[\s>/])~i', '<$1h2', $html);

        return trim(self::purifier()->purify($html));
    }

    public static function text(?string $html): string
    {
        return trim(preg_replace('/\s+/u', ' ', strip_tags(self::clean($html))) ?? '');
    }

    private static function purifier(): HTMLPurifier
    {
        if (self::$purifier === null) {
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', self::cachePath());
            $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,s,del,a[href|title|target|rel],ul,ol,li,blockquote,pre,code,h2,h3,h4,div,span,hr');
            $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
            $config->set('Attr.AllowedFrameTargets', ['_blank']);
            $config->set('HTML.TargetBlank', true);
            $config->set('HTML.Nofollow', true);
            $config->set('AutoFormat.AutoParagraph', true);
            $config->set('AutoFormat.RemoveEmpty', true);
            $config->set('Core.Encoding', 'UTF-8');

            self::$purifier = new HTMLPurifier($config);
        }

        return self::$purifier;
    }

    private static function cachePath(): string
    {
        $path = storage_path('app/purifier');

        if (! is_dir($path)) {
            mkdir($path, 0775, true);
        }

        return $path;
    }
}