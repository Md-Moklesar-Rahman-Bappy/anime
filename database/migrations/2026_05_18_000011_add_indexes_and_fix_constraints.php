<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! $this->hasIndex('anime', 'anime_title_index')) {
            Schema::table('anime', function (Blueprint $table) { $table->index('title'); });
        }
        if (! $this->hasIndex('anime', 'anime_status_index')) {
            Schema::table('anime', function (Blueprint $table) { $table->index('status'); });
        }
        if (! $this->hasIndex('anime', 'anime_featured_index')) {
            Schema::table('anime', function (Blueprint $table) { $table->index('featured'); });
        }

        if (! $this->hasIndex('episodes', 'episodes_anime_id_index')) {
            Schema::table('episodes', function (Blueprint $table) { $table->index('anime_id'); });
        }
        if (! $this->hasIndex('episodes', 'episodes_number_index')) {
            Schema::table('episodes', function (Blueprint $table) { $table->index('number'); });
        }

        if (! $this->hasIndex('manga', 'manga_slug_unique')) {
            Schema::table('manga', function (Blueprint $table) { $table->unique('slug'); });
        }
        if (! $this->hasIndex('manga', 'manga_title_index')) {
            Schema::table('manga', function (Blueprint $table) { $table->index('title'); });
        }
        if (! $this->hasIndex('manga', 'manga_status_index')) {
            Schema::table('manga', function (Blueprint $table) { $table->index('status'); });
        }

        if (! $this->hasIndex('chapters', 'chapters_manga_id_index')) {
            Schema::table('chapters', function (Blueprint $table) { $table->index('manga_id'); });
        }
        if (! $this->hasIndex('chapters', 'chapters_number_index')) {
            Schema::table('chapters', function (Blueprint $table) { $table->index('number'); });
        }

        if (! $this->hasIndex('favorites', 'favorites_user_id_anime_id_category_unique')) {
            Schema::table('favorites', function (Blueprint $table) { $table->unique(['user_id', 'anime_id', 'category'], 'favorites_user_id_anime_id_category_unique'); });
        }

        if (! $this->hasIndex('comments', 'comments_episode_id_index')) {
            Schema::table('comments', function (Blueprint $table) { $table->index('episode_id'); });
        }
        if (! $this->hasIndex('comments', 'comments_user_id_index')) {
            Schema::table('comments', function (Blueprint $table) { $table->index('user_id'); });
        }

        if (! $this->hasIndex('manga_comments', 'manga_comments_chapter_id_index')) {
            Schema::table('manga_comments', function (Blueprint $table) { $table->index('chapter_id'); });
        }
        if (! $this->hasIndex('manga_comments', 'manga_comments_user_id_index')) {
            Schema::table('manga_comments', function (Blueprint $table) { $table->index('user_id'); });
        }

        if (! $this->hasIndex('watch_history', 'watch_history_user_id_index')) {
            Schema::table('watch_history', function (Blueprint $table) { $table->index('user_id'); });
        }
        if (! $this->hasIndex('watch_history', 'watch_history_episode_id_index')) {
            Schema::table('watch_history', function (Blueprint $table) { $table->index('episode_id'); });
        }

        if (! $this->hasIndex('reports', 'reports_episode_id_index')) {
            Schema::table('reports', function (Blueprint $table) { $table->index('episode_id'); });
        }
        if (! $this->hasIndex('reports', 'reports_status_index')) {
            Schema::table('reports', function (Blueprint $table) { $table->index('status'); });
        }

        if (! $this->hasIndex('servers', 'servers_episode_id_index')) {
            Schema::table('servers', function (Blueprint $table) { $table->index('episode_id'); });
        }
        if (! $this->hasIndex('servers', 'servers_type_index')) {
            Schema::table('servers', function (Blueprint $table) { $table->index('type'); });
        }
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        $result = \DB::select("SELECT 1 FROM information_schema.statistics WHERE table_schema = DATABASE() AND table_name = ? AND index_name = ? LIMIT 1", [$table, $indexName]);
        return ! empty($result);
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropIndex(['title']);
            $table->dropIndex(['status']);
            $table->dropIndex(['featured']);
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropIndex(['anime_id']);
            $table->dropIndex(['number']);
        });

        Schema::table('manga', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropIndex(['title']);
            $table->dropIndex(['status']);
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->dropIndex(['manga_id']);
            $table->dropIndex(['number']);
        });

        Schema::table('favorites', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'anime_id', 'category']);
            $table->unique(['user_id', 'anime_id']);
        });

        Schema::table('comments', function (Blueprint $table) {
            $table->dropIndex(['episode_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('manga_comments', function (Blueprint $table) {
            $table->dropIndex(['chapter_id']);
            $table->dropIndex(['user_id']);
        });

        Schema::table('watch_history', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['episode_id']);
        });

        Schema::table('reports', function (Blueprint $table) {
            $table->dropIndex(['episode_id']);
            $table->dropIndex(['status']);
        });

        Schema::table('servers', function (Blueprint $table) {
            $table->dropIndex(['episode_id']);
            $table->dropIndex(['type']);
        });
    }
};
