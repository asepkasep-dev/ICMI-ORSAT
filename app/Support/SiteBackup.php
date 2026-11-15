<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use ZipArchive;

class SiteBackup
{
    public const FORMAT = 1;

    private const APP = 'icmi-orsat';

    public function directory(): string
    {
        return rtrim((string) config('icmi.backup.path'), '/\\');
    }

    /** @return list<string> path lengkap, terbaru lebih dulu */
    public function list(): array
    {
        $files = File::glob($this->directory().DIRECTORY_SEPARATOR.'icmi-backup-*.zip') ?: [];
        rsort($files);

        return $files;
    }

    public function create(string $suffix = ''): string
    {
        File::ensureDirectoryExists($this->directory());

        $path = $this->directory().DIRECTORY_SEPARATOR.'icmi-backup-'.now()->format('Ymd-His-v').($suffix !== '' ? '-'.$suffix : '').'.zip';

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException("Tidak dapat membuat file cadangan di {$path}.");
        }

        $counts = [];
        foreach ($this->tables() as $table) {
            $rows = DB::table($table)->get()->map(fn ($row) => (array) $row)->all();
            $counts[$table] = count($rows);
            $zip->addFromString("database/{$table}.json", json_encode($rows, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));
        }

        $disk = Storage::disk('public');
        $mediaFiles = array_values(array_filter($disk->allFiles(), fn ($file) => ! $this->isHidden($file)));
        foreach ($mediaFiles as $file) {
            $zip->addFile($disk->path($file), 'media/'.$file);
        }

        $zip->addFromString('manifest.json', json_encode([
            'app' => self::APP,
            'format' => self::FORMAT,
            'created_at' => now()->toIso8601String(),
            'app_url' => config('app.url'),
            'driver' => DB::connection()->getDriverName(),
            'migrations' => $this->ranMigrations(),
            'tables' => $counts,
            'media_files' => count($mediaFiles),
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR));

        if (! $zip->close()) {
            throw new RuntimeException("Gagal menulis file cadangan {$path}.");
        }

        return $path;
    }

    /** @return list<string> file yang dihapus */
    public function prune(int $keep): array
    {
        $removed = array_slice($this->list(), max($keep, 1));
        File::delete($removed);

        return $removed;
    }

    /** @return array<string, mixed> */
    public function inspect(string $path): array
    {
        $zip = $this->open($path);
        $manifest = json_decode((string) $zip->getFromName('manifest.json'), true);
        $zip->close();

        if (! is_array($manifest) || ($manifest['app'] ?? null) !== self::APP) {
            throw new RuntimeException('File ini bukan cadangan website ICMI yang valid.');
        }

        if (($manifest['format'] ?? null) !== self::FORMAT) {
            throw new RuntimeException('Format cadangan tidak didukung oleh versi aplikasi ini.');
        }

        return $manifest;
    }

    /**
     * Migrasi yang ada di cadangan tetapi belum dijalankan di sini berarti cadangan
     * berasal dari versi aplikasi yang lebih baru — struktur tabelnya tidak dijamin cocok.
     *
     * @param  array<string, mixed>  $manifest
     * @return array{unknown: list<string>, newer: list<string>}
     */
    public function compareMigrations(array $manifest): array
    {
        $backup = $manifest['migrations'] ?? [];
        $current = $this->ranMigrations();

        return [
            'unknown' => array_values(array_diff($backup, $current)),
            'newer' => array_values(array_diff($current, $backup)),
        ];
    }

    public function restore(string $path): void
    {
        $manifest = $this->inspect($path);

        if ($this->compareMigrations($manifest)['unknown'] !== []) {
            throw new RuntimeException('Cadangan dibuat oleh versi aplikasi yang lebih baru. Perbarui kode dan jalankan migrasi terlebih dahulu.');
        }

        $zip = $this->open($path);
        $staging = sys_get_temp_dir().DIRECTORY_SEPARATOR.'icmi-restore-'.bin2hex(random_bytes(6));

        try {
            $data = [];
            foreach (array_keys($manifest['tables'] ?? []) as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $json = $zip->getFromName("database/{$table}.json");
                if ($json === false) {
                    throw new RuntimeException("Data tabel {$table} tidak ditemukan di dalam cadangan.");
                }

                $data[$table] = json_decode($json, true, flags: JSON_THROW_ON_ERROR);
            }

            // Media diekstrak lebih dulu agar arsip rusak terdeteksi sebelum data apa pun diubah.
            $media = $this->mediaEntries($zip);
            File::ensureDirectoryExists($staging);
            if ($media !== [] && ! $zip->extractTo($staging, array_keys($media))) {
                throw new RuntimeException('Gagal mengekstrak file media dari cadangan.');
            }

            $this->restoreDatabase($data);
            $this->restoreMedia($staging, $media);
        } finally {
            $zip->close();
            File::deleteDirectory($staging);
        }

        $this->invalidateSessions();
        Cache::flush();
    }

    /**
     * ID pengguna di cadangan bisa berbeda dari kondisi sekarang, sehingga sesi lama
     * dapat menunjuk ke akun lain. Semua pengguna wajib masuk ulang.
     */
    private function invalidateSessions(): void
    {
        match (config('session.driver')) {
            'database' => DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->delete(),
            'file' => File::delete(File::files(config('session.files'))),
            default => null,
        };
    }

    /** @param  array<string, list<array<string, mixed>>>  $data */
    private function restoreDatabase(array $data): void
    {
        $order = $this->dependencyOrder(array_keys($data));

        // Di SQLite pragma foreign_keys diabaikan di dalam transaksi, jadi urutan tabel tetap dijaga.
        Schema::disableForeignKeyConstraints();

        try {
            DB::transaction(function () use ($data, $order) {
                // delete(), bukan truncate(): di MySQL truncate memicu commit implisit.
                foreach (array_reverse($order) as $table) {
                    DB::table($table)->delete();
                }

                foreach ($order as $table) {
                    $columns = array_flip(Schema::getColumnListing($table));
                    $rows = array_map(fn ($row) => array_intersect_key($row, $columns), $data[$table]);

                    foreach (array_chunk($rows, 200) as $chunk) {
                        DB::table($table)->insert($chunk);
                    }
                }
            });
        } finally {
            Schema::enableForeignKeyConstraints();
        }
    }

    /** @return array<string, string> nama entri ZIP => path relatif di disk public */
    private function mediaEntries(ZipArchive $zip): array
    {
        $entries = [];
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (! str_starts_with($name, 'media/') || str_ends_with($name, '/')) {
                continue;
            }

            $relative = substr($name, strlen('media/'));
            if ($relative === '' || str_contains($relative, '..') || str_contains($relative, '\\') || str_starts_with($relative, '/')) {
                throw new RuntimeException("Nama file tidak aman di dalam cadangan: {$name}");
            }

            $entries[$name] = $relative;
        }

        return $entries;
    }

    /** @param  array<string, string>  $entries */
    private function restoreMedia(string $staging, array $entries): void
    {
        $disk = Storage::disk('public');

        foreach ($disk->allFiles() as $file) {
            if (! $this->isHidden($file)) {
                $disk->delete($file);
            }
        }

        foreach ($entries as $name => $relative) {
            $stream = fopen($staging.DIRECTORY_SEPARATOR.$name, 'rb');
            if ($stream === false) {
                throw new RuntimeException("Gagal membaca {$name} dari cadangan.");
            }

            $disk->writeStream($relative, $stream);
            fclose($stream);
        }
    }

    /**
     * Urutkan tabel induk sebelum tabel anak berdasarkan foreign key.
     *
     * @param  list<string>  $tables
     * @return list<string>
     */
    private function dependencyOrder(array $tables): array
    {
        $deps = [];
        foreach ($tables as $table) {
            $parents = array_column(Schema::getForeignKeys($table), 'foreign_table');
            $deps[$table] = array_values(array_intersect(array_diff($parents, [$table]), $tables));
        }

        $order = [];
        while ($deps !== []) {
            $ready = array_keys(array_filter($deps, fn ($parents) => array_diff($parents, $order) === []));

            if ($ready === []) {
                $ready = array_keys($deps);
            }

            sort($ready);
            foreach ($ready as $table) {
                $order[] = $table;
                unset($deps[$table]);
            }
        }

        return $order;
    }

    /** @return list<string> */
    private function tables(): array
    {
        $exclude = config('icmi.backup.exclude_tables', []);

        $tables = array_filter(
            Schema::getTableListing(Schema::getCurrentSchemaListing(), false),
            fn ($table) => ! in_array($table, $exclude, true) && ! str_starts_with($table, 'sqlite_'),
        );
        sort($tables);

        return $tables;
    }

    /** @return list<string> */
    private function ranMigrations(): array
    {
        return DB::table('migrations')->orderBy('id')->pluck('migration')->all();
    }

    private function isHidden(string $file): bool
    {
        return collect(explode('/', $file))->contains(fn ($part) => str_starts_with($part, '.'));
    }

    private function open(string $path): ZipArchive
    {
        if (! is_file($path)) {
            throw new RuntimeException("File cadangan tidak ditemukan: {$path}");
        }

        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::RDONLY) !== true) {
            throw new RuntimeException('File cadangan rusak atau bukan arsip ZIP.');
        }

        return $zip;
    }
}
