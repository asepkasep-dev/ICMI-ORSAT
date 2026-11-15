<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\ManagementMember;
use App\Models\Media;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class DatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_reference_columns_have_foreign_keys(): void
    {
        $expected = [
            'articles' => ['user_id' => 'users', 'featured_media_id' => 'media'],
            'media' => ['user_id' => 'users'],
            'organization_profiles' => ['chairman_photo_media_id' => 'media'],
            'management_members' => ['photo_media_id' => 'media'],
        ];

        foreach ($expected as $table => $columns) {
            $keys = collect(Schema::getForeignKeys($table))
                ->mapWithKeys(fn ($fk) => [$fk['columns'][0] => [$fk['foreign_table'], strtolower($fk['on_delete'])]]);

            foreach ($columns as $column => $references) {
                $this->assertSame([$references, 'set null'], $keys[$column] ?? null, "{$table}.{$column}");
            }
        }
    }

    public function test_deleting_referenced_rows_nulls_references(): void
    {
        $user = User::factory()->create();
        $media = Media::create([
            'user_id' => $user->id, 'file_name' => 'a.jpg', 'file_path' => 'media/a.webp', 'disk' => 'public',
            'mime_type' => 'image/webp', 'file_size' => 1, 'width' => 1, 'height' => 1, 'alt' => 'a',
        ]);
        $article = Article::factory()->create(['user_id' => $user->id, 'featured_media_id' => $media->id]);
        $member = ManagementMember::create(['name' => 'Fulan', 'position' => 'Ketua', 'photo_media_id' => $media->id]);

        $media->delete();
        $user->delete();

        $this->assertNull($article->fresh()->featured_media_id);
        $this->assertNull($article->fresh()->user_id);
        $this->assertNull($member->fresh()->photo_media_id);
    }
}
