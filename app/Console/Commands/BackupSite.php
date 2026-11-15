<?php

namespace App\Console\Commands;

use App\Support\SiteBackup;
use Illuminate\Console\Command;
use Throwable;

class BackupSite extends Command
{
    protected $signature = 'icmi:backup {--keep= : Jumlah cadangan terbaru yang disimpan (default dari BACKUP_KEEP)}';

    protected $description = 'Cadangkan basis data dan file media website ke satu arsip ZIP';

    public function handle(SiteBackup $backup): int
    {
        try {
            $path = $backup->create();
        } catch (Throwable $e) {
            report($e);
            $this->error('Pencadangan gagal: '.$e->getMessage());

            return self::FAILURE;
        }

        $manifest = $backup->inspect($path);
        $this->info('Cadangan dibuat: '.$path);
        $this->line(sprintf(
            '%d baris data dari %d tabel, %d file media, %s.',
            array_sum($manifest['tables']),
            count($manifest['tables']),
            $manifest['media_files'],
            $this->size(filesize($path)),
        ));

        $keep = $this->option('keep') !== null ? (int) $this->option('keep') : (int) config('icmi.backup.keep');
        foreach ($backup->prune($keep) as $removed) {
            $this->line('Dihapus (retensi): '.basename($removed));
        }

        return self::SUCCESS;
    }

    private function size(int $bytes): string
    {
        return $bytes >= 1048576 ? round($bytes / 1048576, 1).' MB' : round($bytes / 1024, 1).' KB';
    }
}
