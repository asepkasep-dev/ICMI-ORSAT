<?php

namespace App\Enums;

enum ArticleStatus: string
{
    case Draft = 'draft';
    case InReview = 'in_review';
    case Revision = 'revision';
    case Published = 'published';
    case Scheduled = 'scheduled';
    case Archived = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draf',
            self::InReview => 'Menunggu Review',
            self::Revision => 'Perlu Revisi',
            self::Published => 'Terbit',
            self::Scheduled => 'Terjadwal',
            self::Archived => 'Diarsipkan',
        };
    }
}