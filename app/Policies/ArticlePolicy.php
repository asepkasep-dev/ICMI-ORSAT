<?php

namespace App\Policies;

use App\Enums\ArticleStatus;
use App\Models\Article;
use App\Models\User;
use App\Support\ArticleWorkflow;

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
        return ($user->isAdmin() || $article->user_id === $user->id)
            && ArticleWorkflow::allows('submitReview', $article);
    }

    public function publish(User $user, Article $article): bool
    {
        if (! ArticleWorkflow::allows('publish', $article)) {
            return false;
        }

        // Jadwal ditetapkan Admin; Writer tidak boleh menerbitkan lebih awal.
        return $user->isAdmin()
            || ($article->user_id === $user->id && $article->status !== ArticleStatus::Scheduled);
    }

    public function review(User $user, Article $article): bool
    {
        return $user->isAdmin() && ArticleWorkflow::allows('review', $article);
    }

    public function schedule(User $user, Article $article): bool
    {
        return $user->isAdmin() && ArticleWorkflow::allows('schedule', $article);
    }

    public function archive(User $user, Article $article): bool
    {
        return $user->isAdmin() && ArticleWorkflow::allows('archive', $article);
    }

    public function restore(User $user, Article $article): bool
    {
        return $user->isAdmin() && ArticleWorkflow::allows('restore', $article);
    }
}