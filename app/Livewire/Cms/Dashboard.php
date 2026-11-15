<?php

namespace App\Livewire\Cms;

use App\Enums\ArticleStatus;
use App\Enums\UserRole;
use App\Models\ActivityLog;
use App\Models\Article;
use App\Models\User;
use Livewire\Component;

class Dashboard extends Component
{
    public function render()
    {
        $user = auth()->user();
        $isAdmin = $user->isAdmin();

        $base = Article::query()->when(! $isAdmin, fn ($q) => $q->where('user_id', $user->id));

        $byStatus = (clone $base)
            ->selectRaw('status, count(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $articleStats = [
            'total' => (clone $base)->count(),
            'published' => (int) ($byStatus[ArticleStatus::Published->value] ?? 0),
            'draft' => (int) ($byStatus[ArticleStatus::Draft->value] ?? 0),
            'in_review' => (int) ($byStatus[ArticleStatus::InReview->value] ?? 0),
            'revision' => (int) ($byStatus[ArticleStatus::Revision->value] ?? 0),
            'scheduled' => (int) ($byStatus[ArticleStatus::Scheduled->value] ?? 0),
            'archived' => (int) ($byStatus[ArticleStatus::Archived->value] ?? 0),
        ];

        $userStats = $isAdmin ? [
            'total_users' => User::count(),
            'admins' => User::where('role', UserRole::Admin)->count(),
            'writers' => User::where('role', UserRole::Writer)->count(),
            'inactive' => User::where('is_active', false)->count(),
        ] : [];

        $recentActivity = $isAdmin
            ? ActivityLog::with('user')->latest()->limit(8)->get()
            : collect();

        $recentArticles = (clone $base)
            ->with('featuredMedia')
            ->latest('updated_at')
            ->limit(5)
            ->get();

        return view('livewire.cms.dashboard', [
            'articleStats' => $articleStats,
            'userStats' => $userStats,
            'recentActivity' => $recentActivity,
            'recentArticles' => $recentArticles,
        ])->layout('components.layouts.cms', ['title' => 'Dashboard']);
    }
}