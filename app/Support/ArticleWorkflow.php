<?php

namespace App\Support;

use App\Enums\ArticleStatus;
use App\Models\ActivityLog;
use App\Models\Article;
use Carbon\CarbonInterface;

class ArticleWorkflow
{
    private const ALLOWED_FROM = [
        'submitReview' => [ArticleStatus::Draft, ArticleStatus::Revision],
        // Published -> publish menyimpan pembaruan konten tanpa mengubah waktu tayang.
        'publish' => [ArticleStatus::Draft, ArticleStatus::InReview, ArticleStatus::Revision, ArticleStatus::Scheduled, ArticleStatus::Published],
        'schedule' => [ArticleStatus::Draft, ArticleStatus::InReview, ArticleStatus::Revision, ArticleStatus::Scheduled],
        'review' => [ArticleStatus::Draft, ArticleStatus::InReview, ArticleStatus::Revision, ArticleStatus::Scheduled],
        'archive' => [ArticleStatus::Published, ArticleStatus::Scheduled],
        'restore' => [ArticleStatus::Archived],
    ];

    public static function allows(string $action, Article $article): bool
    {
        return in_array($article->status ?? ArticleStatus::Draft, self::ALLOWED_FROM[$action], true);
    }

    public static function submitReview(Article $article): void
    {
        $article->status = ArticleStatus::InReview;
        $article->revision_note = null;
        $article->save();

        ActivityLog::record('article.submitted_review', $article, 'Mengajukan berita "'.$article->title.'" untuk review');
    }

    public static function publish(Article $article): void
    {
        if ($article->status !== ArticleStatus::Published) {
            $article->published_at = now();
        }
        $article->status = ArticleStatus::Published;
        $article->revision_note = null;
        $article->save();

        ActivityLog::record('article.published', $article, 'Menerbitkan berita "'.$article->title.'"');
    }

    public static function requestRevision(Article $article, string $note): void
    {
        $article->status = ArticleStatus::Revision;
        $article->revision_note = $note;
        $article->save();

        ActivityLog::record('article.revision_requested', $article, 'Meminta revisi berita "'.$article->title.'"');
    }

    public static function schedule(Article $article, CarbonInterface $at): void
    {
        $article->status = ArticleStatus::Scheduled;
        $article->published_at = $at;
        $article->revision_note = null;
        $article->save();

        ActivityLog::record('article.scheduled', $article, 'Menjadwalkan berita "'.$article->title.'" pada '.$at->translatedFormat('d M Y H:i'));
    }

    public static function archive(Article $article): void
    {
        $article->status = ArticleStatus::Archived;
        $article->save();

        ActivityLog::record('article.archived', $article, 'Mengarsipkan berita "'.$article->title.'"');
    }

    public static function restore(Article $article): void
    {
        $article->status = ArticleStatus::Draft;
        $article->revision_note = null;
        $article->save();

        ActivityLog::record('article.restored', $article, 'Memulihkan berita "'.$article->title.'" ke draf');
    }
}