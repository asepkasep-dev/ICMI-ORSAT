<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\Media;
use App\Models\User;

class MediaPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Media $media): bool
    {
        return $user->isAdmin()
            || $media->user_id === $user->id
            || $media->articles()->withTrashed()->where('articles.user_id', $user->id)->exists()
            || Article::withTrashed()->where('featured_media_id', $media->id)->where('user_id', $user->id)->exists();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Media $media): bool
    {
        return $user->isAdmin() || $media->user_id === $user->id;
    }

    public function delete(User $user, Media $media): bool
    {
        return $user->isAdmin() || $media->user_id === $user->id;
    }
}