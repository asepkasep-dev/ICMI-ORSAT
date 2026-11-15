<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const KEYS = [
        ['articles', 'user_id', 'users'],
        ['articles', 'featured_media_id', 'media'],
        ['media', 'user_id', 'users'],
        ['organization_profiles', 'chairman_photo_media_id', 'media'],
        ['management_members', 'photo_media_id', 'media'],
    ];

    public function up(): void
    {
        foreach (self::KEYS as [$table, $column, $references]) {
            // Referensi yatim dari sebelum constraint ada akan menggagalkan pembuatan foreign key.
            DB::table($table)
                ->whereNotNull($column)
                ->whereNotIn($column, DB::table($references)->select('id'))
                ->update([$column => null]);

            Schema::table($table, function (Blueprint $blueprint) use ($column, $references) {
                $blueprint->foreign($column)->references('id')->on($references)->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        foreach (self::KEYS as [$table, $column]) {
            Schema::table($table, function (Blueprint $blueprint) use ($column) {
                $blueprint->dropForeign([$column]);
            });
        }
    }
};
