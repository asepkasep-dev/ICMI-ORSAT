<?php

namespace App\Policies;

use App\Models\Article;
use App\Models\User;

class ArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Article $article): bool
    {
        return $user->isAdmin() || $article->user_id === $user->id || $article->isPublished();
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Article $article): bool
    {
        return $user->isAdmin() || $article->user_id === $user->id;
    }

    public function delete(User $user, Article $article): bool
    {
        return $user->isAdmin();
    }

    public function submitReview(User $user, Article $article): bool
    {
        return $user->isAdmin() || $article->user_id === $user->id;
    }

    public function publish(User $user, Article $article): bool
    {
        return $user->isAdmin() || $article->user_id === $user->id;
    }

    public function review(User $user, Article $article): bool
    {
        return $user->isAdmin();
    }

    public function schedule(User $user, Article $article): bool
    {
        return $user->isAdmin();
    }

    public function archive(User $user, Article $article): bool
    {
        return $user->isAdmin();
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->isAdmin();
    }
}