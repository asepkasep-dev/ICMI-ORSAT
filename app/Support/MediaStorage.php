<?php

namespace App\Support;

use App\Models\Media;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class MediaStorage
{
    public static function store(UploadedFile|TemporaryUploadedFile $upload, ?int $userId = null): Media
    {
        $userId ??= auth()->id();
        $originalName = $upload->getClientOriginalName();

        if (extension_loaded('gd')) {
            $manager = new ImageManager(new Driver());
            $image = $manager->decodePath($upload->getRealPath())->orient()->scaleDown(width: 1600);
            $path = 'media/'.Str::uuid()->toString().'.webp';
            Storage::disk('public')->put($path, (string) $image->encode(new WebpEncoder(quality: 82, strip: true)));
            [$width, $height] = [$image->width(), $image->height()];
            $mime = 'image/webp';
            $size = Storage::disk('public')->size($path);
        } else {
            $path = $upload->store('media', 'public');
            [$width, $height] = getimagesize(Storage::disk('public')->path($path));
            $mime = $upload->getMimeType();
            $size = $upload->getSize();
        }

        return Media::create([
            'user_id' => $userId,
            'file_name' => $originalName,
            'file_path' => $path,
            'disk' => 'public',
            'mime_type' => $mime,
            'file_size' => $size,
            'width' => $width,
            'height' => $height,
            'alt' => pathinfo($originalName, PATHINFO_FILENAME),
        ]);
    }

    public static function deleteFile(Media $media): void
    {
        if ($media->disk === 'public' && ! str_starts_with($media->file_path, 'media/demo/')) {
            Storage::disk('public')->delete($media->file_path);
        }
    }
}