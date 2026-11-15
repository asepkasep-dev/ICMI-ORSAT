<?php

namespace App\Console\Commands;

use App\Enums\ArticleStatus;
use App\Models\ActivityLog;
use App\Models\Article;
use Illuminate\Console\Command;

class PublishScheduledArticles extends Command
{
    protected $signature = 'articles:publish-scheduled';

    protected $description = 'Terbitkan artikel terjadwal yang telah mencapai waktu publikasi';

    public function handle(): int
    {
        $due = Article::where('status', ArticleStatus::Scheduled)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get();

        foreach ($due as $article) {
            $article->update(['status' => ArticleStatus::Published]);
            ActivityLog::record('article.published', $article, 'Publikasi terjadwal otomatis: "'.$article->title.'"');
        }

        $this->info($due->count().' artikel diterbitkan.');

        return self::SUCCESS;
    }
}