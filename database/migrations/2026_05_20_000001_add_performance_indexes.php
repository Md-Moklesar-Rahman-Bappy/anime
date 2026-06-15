<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Anime
        if (!$this->hasIndex('anime', 'anime_views_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->index('views', 'anime_views_index');
            });
        }

        if (!$this->hasIndex('anime', 'anime_created_at_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->index('created_at', 'anime_created_at_index');
            });
        }

        if (!$this->hasIndex('anime', 'anime_featured_order_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->index(['featured', 'featured_order'], 'anime_featured_order_index');
            });
        }

        // Episodes
        if (!$this->hasIndex('episodes', 'episodes_created_at_index')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->index('created_at', 'episodes_created_at_index');
            });
        }

        if (!$this->hasIndex('episodes', 'episodes_anime_id_number_index')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->index(['anime_id', 'number'], 'episodes_anime_id_number_index');
            });
        }

        // Manga
        if (!$this->hasIndex('manga', 'manga_views_index')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->index('views', 'manga_views_index');
            });
        }

        if (!$this->hasIndex('manga', 'manga_created_at_index')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->index('created_at', 'manga_created_at_index');
            });
        }

        // Chapters
        if (!$this->hasIndex('chapters', 'chapters_created_at_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->index('created_at', 'chapters_created_at_index');
            });
        }
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::select(
            'SELECT 1 FROM information_schema.statistics 
             WHERE table_schema = DATABASE() 
             AND table_name = ? 
             AND index_name = ? 
             LIMIT 1',
            [$table, $indexName]
        );

        return !empty($result);
    }

    public function down(): void
    {
        // Anime
        if ($this->hasIndex('anime', 'anime_views_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->dropIndex('anime_views_index');
            });
        }

        if ($this->hasIndex('anime', 'anime_created_at_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->dropIndex('anime_created_at_index');
            });
        }

        if ($this->hasIndex('anime', 'anime_featured_order_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->dropIndex('anime_featured_order_index');
            });
        }

        // Episodes
        if ($this->hasIndex('episodes', 'episodes_created_at_index')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->dropIndex('episodes_created_at_index');
            });
        }

        if ($this->hasIndex('episodes', 'episodes_anime_id_number_index')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->dropIndex('episodes_anime_id_number_index');
            });
        }

        // Manga
        if ($this->hasIndex('manga', 'manga_views_index')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->dropIndex('manga_views_index');
            });
        }

        if ($this->hasIndex('manga', 'manga_created_at_index')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->dropIndex('manga_created_at_index');
            });
        }

        // Chapters
        if ($this->hasIndex('chapters', 'chapters_created_at_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->dropIndex('chapters_created_at_index');
            });
        }
    }
};
