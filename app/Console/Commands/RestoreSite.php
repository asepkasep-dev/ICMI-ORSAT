<?php

namespace App\Console\Commands;

use App\Support\SiteBackup;
use Illuminate\Console\Command;
use Throwable;

class RestoreSite extends Command
{
    protected $signature = 'icmi:restore
        {file? : Nama atau path file cadangan; kosongkan untuk melihat daftar cadangan}
        {--force : Jalankan tanpa konfirmasi}
        {--no-safety-backup : Jangan buat cadangan kondisi saat ini sebelum memulihkan}';

    protected $description = 'Pulihkan basis data dan file media dari arsip cadangan';

    public function handle(SiteBackup $backup): int
    {
        $file = $this->argument('file');

        if ($file === null) {
            return $this->listBackups($backup);
        }

        $path = is_file($file) ? $file : $backup->directory().DIRECTORY_SEPARATOR.basename($file);

        try {
            $manifest = $backup->inspect($path);
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->table(['Keterangan', 'Nilai'], [
            ['File', basename($path)],
            ['Dibuat', $manifest['created_at']],
            ['Asal', $manifest['app_url'].' ('.$manifest['driver'].')'],
            ['Baris data', array_sum($manifest['tables'])],
            ['File media', $manifest['media_files']],
        ]);

        $migrations = $backup->compareMigrations($manifest);
        if ($migrations['unknown'] !== []) {
            $this->error('Cadangan dibuat oleh versi aplikasi yang lebih baru. Migrasi berikut belum ada di sini:');
            $this->line(collect($migrations['unknown'])->map(fn ($m) => '  - '.$m)->implode(PHP_EOL));

            return self::FAILURE;
        }

        if ($migrations['newer'] !== []) {
            $this->warn('Cadangan berasal dari versi lebih lama; kolom baru akan memakai nilai bawaan. Migrasi yang lebih baru:');
            $this->line(collect($migrations['newer'])->map(fn ($m) => '  - '.$m)->implode(PHP_EOL));
        }

        $this->warn('Seluruh data dan file media saat ini akan DIGANTI dengan isi cadangan.');
        if (! $this->option('force') && ! $this->confirm('Lanjutkan pemulihan?')) {
            $this->line('Dibatalkan.');

            return self::FAILURE;
        }

        $wasDown = app()->isDownForMaintenance();

        try {
            if (! $this->option('no-safety-backup')) {
                $this->line('Cadangan pengaman kondisi saat ini: '.$backup->create('pra-pemulihan'));
            }

            if (! $wasDown) {
                $this->callSilently('down');
            }

            $backup->restore($path);
        } catch (Throwable $e) {
            report($e);
            $this->error('Pemulihan gagal: '.$e->getMessage());

            return self::FAILURE;
        } finally {
            if (! $wasDown) {
                $this->callSilently('up');
            }
        }

        $this->info('Pemulihan selesai. Semua pengguna perlu masuk kembali.');

        return self::SUCCESS;
    }

    private function listBackups(SiteBackup $backup): int
    {
        $files = $backup->list();

        if ($files === []) {
            $this->warn('Belum ada cadangan di '.$backup->directory());

            return self::SUCCESS;
        }

        $this->table(['File', 'Ukuran'], array_map(
            fn ($file) => [basename($file), round(filesize($file) / 1048576, 2).' MB'],
            $files,
        ));
        $this->line('Jalankan: php artisan icmi:restore <nama-file>');

        return self::SUCCESS;
    }
}
