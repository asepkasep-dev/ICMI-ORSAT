<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\Category;
use App\Models\Media;
use App\Models\Setting;
use App\Models\User;
use App\Support\SiteBackup;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class BackupRestoreTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'icmi-backups-'.uniqid();
        config(['icmi.backup.path' => $this->dir, 'icmi.backup.keep' => 14]);
        Storage::fake('public');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    public function test_backup_then_restore_returns_site_to_backed_up_state(): void
    {
        $author = User::factory()->create(['name' => 'Penulis Asli']);
        $category = Category::factory()->create(['name' => 'Kegiatan', 'slug' => 'kegiatan']);
        Storage::disk('public')->put('media/2026/11/foto.jpg', 'isi-gambar-asli');
        $media = Media::create([
            'user_id' => $author->id, 'file_name' => 'foto.jpg', 'file_path' => 'media/2026/11/foto.jpg',
            'disk' => 'public', 'mime_type' => 'image/jpeg', 'file_size' => 15,
        ]);
        $article = Article::factory()->published()->create([
            'user_id' => $author->id, 'title' => 'Berita Asli', 'slug' => 'berita-asli', 'featured_media_id' => $media->id,
        ]);
        $article->categories()->attach($category);
        $article->photos()->attach($media, ['show_in_gallery' => true, 'sort_order' => 1]);
        Setting::create(['key' => 'site.name', 'value' => 'ICMI Orsat Caringin']);

        $this->artisan('icmi:backup')->assertSuccessful();
        $files = File::glob($this->dir.'/icmi-backup-*.zip');
        $this->assertCount(1, $files);

        $article->forceDelete();
        $category->delete();
        Category::factory()->create(['slug' => 'kategori-baru']);
        $author->update(['name' => 'Nama Diubah']);
        Setting::query()->delete();
        Storage::disk('public')->delete('media/2026/11/foto.jpg');
        Storage::disk('public')->put('media/sampah.jpg', 'tidak-ada-di-cadangan');

        $this->artisan('icmi:restore', ['file' => basename($files[0]), '--force' => true, '--no-safety-backup' => true])
            ->assertSuccessful();

        $restored = Article::where('slug', 'berita-asli')->firstOrFail();
        $this->assertSame($article->id, $restored->id);
        $this->assertSame($media->id, $restored->featured_media_id);
        $this->assertSame(['kegiatan'], $restored->categories->pluck('slug')->all());
        $this->assertSame([$media->id], $restored->photos->pluck('id')->all());
        $this->assertFalse(Category::where('slug', 'kategori-baru')->exists());
        $this->assertSame('Penulis Asli', $author->fresh()->name);
        $this->assertSame('ICMI Orsat Caringin', Setting::where('key', 'site.name')->value('value'));
        $this->assertSame('isi-gambar-asli', Storage::disk('public')->get('media/2026/11/foto.jpg'));
        Storage::disk('public')->assertMissing('media/sampah.jpg');

        $this->get('/berita/berita-asli')->assertOk()->assertSee('Berita Asli');
    }

    public function test_restore_creates_safety_backup_by_default(): void
    {
        $path = app(SiteBackup::class)->create();

        $this->artisan('icmi:restore', ['file' => $path, '--force' => true])->assertSuccessful();

        $this->assertCount(1, File::glob($this->dir.'/icmi-backup-*-pra-pemulihan.zip'));
    }

    public function test_restore_asks_for_confirmation_and_can_be_cancelled(): void
    {
        User::factory()->create(['name' => 'Tetap']);
        $path = app(SiteBackup::class)->create();
        User::query()->update(['name' => 'Berubah']);

        $this->artisan('icmi:restore', ['file' => $path])
            ->expectsConfirmation('Lanjutkan pemulihan?', 'no')
            ->assertFailed();

        $this->assertSame('Berubah', User::value('name'));
    }

    public function test_backup_excludes_sessions_cache_and_migrations_tables(): void
    {
        $path = app(SiteBackup::class)->create();
        $manifest = app(SiteBackup::class)->inspect($path);

        $this->assertArrayHasKey('users', $manifest['tables']);
        $this->assertArrayHasKey('articles', $manifest['tables']);
        foreach (['migrations', 'sessions', 'cache', 'jobs', 'password_reset_tokens'] as $table) {
            $this->assertArrayNotHasKey($table, $manifest['tables']);
        }
        $this->assertNotEmpty($manifest['migrations']);
    }

    public function test_old_backups_are_pruned_beyond_retention(): void
    {
        File::ensureDirectoryExists($this->dir);
        foreach (['20260101-020000-000', '20260102-020000-000', '20260103-020000-000'] as $stamp) {
            File::put("{$this->dir}/icmi-backup-{$stamp}.zip", 'lama');
        }

        $this->artisan('icmi:backup', ['--keep' => 2])->assertSuccessful();

        $names = array_map('basename', File::glob($this->dir.'/icmi-backup-*.zip'));
        $this->assertCount(2, $names);
        $this->assertContains('icmi-backup-20260103-020000-000.zip', $names);
        $this->assertNotContains('icmi-backup-20260101-020000-000.zip', $names);
    }

    public function test_restore_rejects_invalid_files(): void
    {
        File::ensureDirectoryExists($this->dir);
        File::put($this->dir.'/bukan-zip.zip', 'teks biasa');

        $this->artisan('icmi:restore', ['file' => 'bukan-zip.zip', '--force' => true])->assertFailed();
        $this->artisan('icmi:restore', ['file' => 'tidak-ada.zip', '--force' => true])->assertFailed();
    }

    public function test_restore_rejects_backup_from_newer_application_version(): void
    {
        $path = app(SiteBackup::class)->create();
        $this->rewriteManifest($path, function (array $manifest) {
            $manifest['migrations'][] = '2099_01_01_000000_migrasi_masa_depan';

            return $manifest;
        });
        User::factory()->create();

        $this->artisan('icmi:restore', ['file' => $path, '--force' => true, '--no-safety-backup' => true])
            ->expectsOutputToContain('2099_01_01_000000_migrasi_masa_depan')
            ->assertFailed();

        $this->assertSame(1, User::count());
    }

    public function test_restore_logs_everyone_out(): void
    {
        config(['session.driver' => 'database']);
        $path = app(SiteBackup::class)->create();
        $user = User::factory()->create();
        DB::table('sessions')->insert([
            'id' => 'sesi-lama', 'user_id' => $user->id, 'ip_address' => '127.0.0.1',
            'user_agent' => 'test', 'payload' => '', 'last_activity' => now()->timestamp,
        ]);

        $this->artisan('icmi:restore', ['file' => $path, '--force' => true, '--no-safety-backup' => true])
            ->assertSuccessful();

        $this->assertSame(0, DB::table('sessions')->count());
    }

    public function test_restore_leaves_site_online_afterwards(): void
    {
        $path = app(SiteBackup::class)->create();

        $this->artisan('icmi:restore', ['file' => $path, '--force' => true, '--no-safety-backup' => true])
            ->assertSuccessful();

        $this->assertFalse($this->app->isDownForMaintenance());
    }

    public function test_restore_keeps_site_down_if_it_was_already_in_maintenance(): void
    {
        $path = app(SiteBackup::class)->create();
        $this->artisan('down')->assertSuccessful();

        try {
            $this->artisan('icmi:restore', ['file' => $path, '--force' => true, '--no-safety-backup' => true])
                ->assertSuccessful();

            $this->assertTrue($this->app->isDownForMaintenance());
        } finally {
            $this->artisan('up');
        }
    }

    public function test_restore_rejects_unsafe_media_paths_before_touching_data(): void
    {
        $path = app(SiteBackup::class)->create();
        $zip = new ZipArchive;
        $zip->open($path);
        $zip->addFromString('media/../../evil.php', '<?php echo 1;');
        $zip->close();
        User::factory()->create();

        $this->artisan('icmi:restore', ['file' => $path, '--force' => true, '--no-safety-backup' => true])
            ->expectsOutputToContain('tidak aman')
            ->assertFailed();

        $this->assertSame(1, User::count());
    }

    public function test_restore_without_file_lists_available_backups(): void
    {
        $path = app(SiteBackup::class)->create();

        $this->artisan('icmi:restore')
            ->expectsOutputToContain(basename($path))
            ->assertSuccessful();
    }

    public function test_backup_is_scheduled_daily(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('icmi:backup')
            ->assertSuccessful();
    }

    private function rewriteManifest(string $path, callable $change): void
    {
        $zip = new ZipArchive;
        $zip->open($path);
        $manifest = $change(json_decode($zip->getFromName('manifest.json'), true));
        $zip->addFromString('manifest.json', json_encode($manifest));
        $zip->close();
    }
}
