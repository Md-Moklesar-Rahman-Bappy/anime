<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Anime
        if (! $this->hasIndex('anime', 'anime_title_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->index('title');
            });
        }

        if (! $this->hasIndex('anime', 'anime_status_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->index('status');
            });
        }

        if (! $this->hasIndex('anime', 'anime_featured_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->index('featured');
            });
        }

        // Episodes
        if (! $this->hasIndex('episodes', 'episodes_anime_id_index')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->index('anime_id');
            });
        }

        if (! $this->hasIndex('episodes', 'episodes_number_index')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->index('number');
            });
        }

        // Manga
        if (! $this->hasIndex('manga', 'manga_slug_unique')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->unique('slug');
            });
        }

        if (! $this->hasIndex('manga', 'manga_title_index')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->index('title');
            });
        }

        if (! $this->hasIndex('manga', 'manga_status_index')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->index('status');
            });
        }

        // Chapters
        if (! $this->hasIndex('chapters', 'chapters_manga_id_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->index('manga_id');
            });
        }

        if (! $this->hasIndex('chapters', 'chapters_number_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->index('number');
            });
        }

        // Favorites
        if (! $this->hasIndex('favorites', 'favorites_user_id_anime_id_category_unique')) {
            // Drop old unique if it exists: unique(user_id, anime_id)
            if ($this->hasIndex('favorites', 'favorites_user_id_anime_id_unique')) {
                Schema::table('favorites', function (Blueprint $table) {
                    $table->dropUnique('favorites_user_id_anime_id_unique');
                });
            }

            Schema::table('favorites', function (Blueprint $table) {
                $table->unique(
                    ['user_id', 'anime_id', 'category'],
                    'favorites_user_id_anime_id_category_unique'
                );
            });
        }

        // Comments
        if (! $this->hasIndex('comments', 'comments_episode_id_index')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->index('episode_id');
            });
        }

        if (! $this->hasIndex('comments', 'comments_user_id_index')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        // Manga Comments
        if (! $this->hasIndex('manga_comments', 'manga_comments_chapter_id_index')) {
            Schema::table('manga_comments', function (Blueprint $table) {
                $table->index('chapter_id');
            });
        }

        if (! $this->hasIndex('manga_comments', 'manga_comments_user_id_index')) {
            Schema::table('manga_comments', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        // Watch History
        if (! $this->hasIndex('watch_history', 'watch_history_user_id_index')) {
            Schema::table('watch_history', function (Blueprint $table) {
                $table->index('user_id');
            });
        }

        if (! $this->hasIndex('watch_history', 'watch_history_episode_id_index')) {
            Schema::table('watch_history', function (Blueprint $table) {
                $table->index('episode_id');
            });
        }

        // Reports
        if (! $this->hasIndex('reports', 'reports_episode_id_index')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->index('episode_id');
            });
        }

        if (! $this->hasIndex('reports', 'reports_status_index')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->index('status');
            });
        }

        // Servers
        if (! $this->hasIndex('servers', 'servers_episode_id_index')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->index('episode_id');
            });
        }

        if (! $this->hasIndex('servers', 'servers_type_index')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->index('type');
            });
        }
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::select(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [$table, $indexName]
        );

        return ! empty($result);
    }

    public function down(): void
    {
        // Anime
        if ($this->hasIndex('anime', 'anime_title_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->dropIndex('anime_title_index');
            });
        }

        if ($this->hasIndex('anime', 'anime_status_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->dropIndex('anime_status_index');
            });
        }

        if ($this->hasIndex('anime', 'anime_featured_index')) {
            Schema::table('anime', function (Blueprint $table) {
                $table->dropIndex('anime_featured_index');
            });
        }

        // Episodes
        if ($this->hasIndex('episodes', 'episodes_anime_id_index')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->dropIndex('episodes_anime_id_index');
            });
        }

        if ($this->hasIndex('episodes', 'episodes_number_index')) {
            Schema::table('episodes', function (Blueprint $table) {
                $table->dropIndex('episodes_number_index');
            });
        }

        // Manga
        if ($this->hasIndex('manga', 'manga_slug_unique')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->dropUnique('manga_slug_unique');
            });
        }

        if ($this->hasIndex('manga', 'manga_title_index')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->dropIndex('manga_title_index');
            });
        }

        if ($this->hasIndex('manga', 'manga_status_index')) {
            Schema::table('manga', function (Blueprint $table) {
                $table->dropIndex('manga_status_index');
            });
        }

        // Chapters
        if ($this->hasIndex('chapters', 'chapters_manga_id_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->dropIndex('chapters_manga_id_index');
            });
        }

        if ($this->hasIndex('chapters', 'chapters_number_index')) {
            Schema::table('chapters', function (Blueprint $table) {
                $table->dropIndex('chapters_number_index');
            });
        }

        // Favorites
        if ($this->hasIndex('favorites', 'favorites_user_id_anime_id_category_unique')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropUnique('favorites_user_id_anime_id_category_unique');
            });
        }

        if (! $this->hasIndex('favorites', 'favorites_user_id_anime_id_unique')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->unique(['user_id', 'anime_id'], 'favorites_user_id_anime_id_unique');
            });
        }

        // Comments
        if ($this->hasIndex('comments', 'comments_episode_id_index')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropIndex('comments_episode_id_index');
            });
        }

        if ($this->hasIndex('comments', 'comments_user_id_index')) {
            Schema::table('comments', function (Blueprint $table) {
                $table->dropIndex('comments_user_id_index');
            });
        }

        // Manga Comments
        if ($this->hasIndex('manga_comments', 'manga_comments_chapter_id_index')) {
            Schema::table('manga_comments', function (Blueprint $table) {
                $table->dropIndex('manga_comments_chapter_id_index');
            });
        }

        if ($this->hasIndex('manga_comments', 'manga_comments_user_id_index')) {
            Schema::table('manga_comments', function (Blueprint $table) {
                $table->dropIndex('manga_comments_user_id_index');
            });
        }

        // Watch History
        if ($this->hasIndex('watch_history', 'watch_history_user_id_index')) {
            Schema::table('watch_history', function (Blueprint $table) {
                $table->dropIndex('watch_history_user_id_index');
            });
        }

        if ($this->hasIndex('watch_history', 'watch_history_episode_id_index')) {
            Schema::table('watch_history', function (Blueprint $table) {
                $table->dropIndex('watch_history_episode_id_index');
            });
        }

        // Reports
        if ($this->hasIndex('reports', 'reports_episode_id_index')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropIndex('reports_episode_id_index');
            });
        }

        if ($this->hasIndex('reports', 'reports_status_index')) {
            Schema::table('reports', function (Blueprint $table) {
                $table->dropIndex('reports_status_index');
            });
        }

        // Servers
        if ($this->hasIndex('servers', 'servers_episode_id_index')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->dropIndex('servers_episode_id_index');
            });
        }

        if ($this->hasIndex('servers', 'servers_type_index')) {
            Schema::table('servers', function (Blueprint $table) {
                $table->dropIndex('servers_type_index');
            });
        }
    }
};
