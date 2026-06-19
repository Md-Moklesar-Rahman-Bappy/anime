<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Anime Indexes
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('anime', ['title'], 'anime_title_index');
        $this->addIndexIfMissing('anime', ['status'], 'anime_status_index');
        $this->addIndexIfMissing('anime', ['type'], 'anime_type_index');
        $this->addIndexIfMissing('anime', ['featured'], 'anime_featured_index');
        $this->addIndexIfMissing('anime', ['views'], 'anime_views_index');

        /*
        |--------------------------------------------------------------------------
        | Episodes Indexes
        |--------------------------------------------------------------------------
        | Do NOT add anime_id index manually because foreignId already creates it.
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('episodes', ['number'], 'episodes_number_index');
        $this->addIndexIfMissing('episodes', ['source_type'], 'episodes_source_type_index');
        $this->addIndexIfMissing('episodes', ['views'], 'episodes_views_index');
        $this->addIndexIfMissing('episodes', ['air_date'], 'episodes_air_date_index');

        /*
        |--------------------------------------------------------------------------
        | Manga Indexes
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('manga', ['title'], 'manga_title_index');
        $this->addIndexIfMissing('manga', ['status'], 'manga_status_index');
        $this->addIndexIfMissing('manga', ['type'], 'manga_type_index');
        $this->addIndexIfMissing('manga', ['featured'], 'manga_featured_index');
        $this->addIndexIfMissing('manga', ['views'], 'manga_views_index');

        /*
        |--------------------------------------------------------------------------
        | Chapters Indexes
        | Do NOT add manga_id index manually because foreignId already creates it.
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('chapters', ['number'], 'chapters_number_index');

        /*
        |--------------------------------------------------------------------------
        | Manga Pages Indexes
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('manga_pages', ['page_number'], 'manga_pages_page_number_index');

        /*
        |--------------------------------------------------------------------------
        | Favorites Indexes
        | Keep original unique(user_id, anime_id). Do NOT replace it with category
        | unless your app allows same anime in multiple lists.
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('favorites', ['category'], 'favorites_category_index');

        /*
        |--------------------------------------------------------------------------
        | Manga Favorites Indexes
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('manga_favorites', ['category'], 'manga_favorites_category_index');

        /*
        |--------------------------------------------------------------------------
        | Comments Indexes
        | Do NOT add episode_id/user_id manually because foreign keys already index them.
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('comments', ['status'], 'comments_status_index');
        $this->addIndexIfMissing('manga_comments', ['status'], 'manga_comments_status_index');

        /*
        |--------------------------------------------------------------------------
        | Watch History Indexes
        | Do NOT add user_id/episode_id manually because foreign keys already index them.
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('watch_history', ['completed'], 'watch_history_completed_index');

        /*
        |--------------------------------------------------------------------------
        | Reports Indexes
        | IMPORTANT: reports table uses morphs(reportable), not episode_id.
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('reports', ['issue_type'], 'reports_issue_type_index');
        $this->addIndexIfMissing('reports', ['status'], 'reports_status_index');

        /*
        |--------------------------------------------------------------------------
        | Requests Indexes
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('anime_requests', ['status'], 'anime_requests_status_index');
        $this->addIndexIfMissing('anime_requests', ['anime_title'], 'anime_requests_anime_title_index');

        /*
        |--------------------------------------------------------------------------
        | Servers Indexes
        | Do NOT add episode_id manually because foreignId already creates it.
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('servers', ['type'], 'servers_type_index');
        $this->addIndexIfMissing('servers', ['language'], 'servers_language_index');
        $this->addIndexIfMissing('servers', ['priority'], 'servers_priority_index');

        /*
        |--------------------------------------------------------------------------
        | Settings Indexes
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('settings', ['group'], 'settings_group_index');

        /*
        |--------------------------------------------------------------------------
        | Chunked Uploads Indexes
        |--------------------------------------------------------------------------
        */
        $this->addIndexIfMissing('chunked_uploads', ['status'], 'chunked_uploads_status_index');
    }

    public function down(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Anime
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('anime', 'anime_title_index');
        $this->dropIndexIfExists('anime', 'anime_status_index');
        $this->dropIndexIfExists('anime', 'anime_type_index');
        $this->dropIndexIfExists('anime', 'anime_featured_index');
        $this->dropIndexIfExists('anime', 'anime_views_index');

        /*
        |--------------------------------------------------------------------------
        | Episodes
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('episodes', 'episodes_number_index');
        $this->dropIndexIfExists('episodes', 'episodes_source_type_index');
        $this->dropIndexIfExists('episodes', 'episodes_views_index');
        $this->dropIndexIfExists('episodes', 'episodes_air_date_index');

        /*
        |--------------------------------------------------------------------------
        | Manga
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('manga', 'manga_title_index');
        $this->dropIndexIfExists('manga', 'manga_status_index');
        $this->dropIndexIfExists('manga', 'manga_type_index');
        $this->dropIndexIfExists('manga', 'manga_featured_index');
        $this->dropIndexIfExists('manga', 'manga_views_index');

        /*
        |--------------------------------------------------------------------------
        | Chapters / Pages
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('chapters', 'chapters_number_index');
        $this->dropIndexIfExists('manga_pages', 'manga_pages_page_number_index');

        /*
        |--------------------------------------------------------------------------
        | Favorites
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('favorites', 'favorites_category_index');
        $this->dropIndexIfExists('manga_favorites', 'manga_favorites_category_index');

        /*
        |--------------------------------------------------------------------------
        | Comments
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('comments', 'comments_status_index');
        $this->dropIndexIfExists('manga_comments', 'manga_comments_status_index');

        /*
        |--------------------------------------------------------------------------
        | Watch History
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('watch_history', 'watch_history_completed_index');

        /*
        |--------------------------------------------------------------------------
        | Reports
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('reports', 'reports_issue_type_index');
        $this->dropIndexIfExists('reports', 'reports_status_index');

        /*
        |--------------------------------------------------------------------------
        | Requests
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('anime_requests', 'anime_requests_status_index');
        $this->dropIndexIfExists('anime_requests', 'anime_requests_anime_title_index');

        /*
        |--------------------------------------------------------------------------
        | Servers
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('servers', 'servers_type_index');
        $this->dropIndexIfExists('servers', 'servers_language_index');
        $this->dropIndexIfExists('servers', 'servers_priority_index');

        /*
        |--------------------------------------------------------------------------
        | Settings
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('settings', 'settings_group_index');

        /*
        |--------------------------------------------------------------------------
        | Chunked Uploads
        |--------------------------------------------------------------------------
        */
        $this->dropIndexIfExists('chunked_uploads', 'chunked_uploads_status_index');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    protected function addIndexIfMissing(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        foreach ($columns as $column) {
            if (!Schema::hasColumn($table, $column)) {
                return;
            }
        }

        if ($this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($columns, $indexName) {
            $blueprint->index($columns, $indexName);
        });
    }

    protected function dropIndexIfExists(string $table, string $indexName): void
    {
        if (!Schema::hasTable($table)) {
            return;
        }

        if (!$this->hasIndex($table, $indexName)) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
            $blueprint->dropIndex($indexName);
        });
    }

    protected function hasIndex(string $table, string $indexName): bool
    {
        if (!Schema::hasTable($table)) {
            return false;
        }

        $result = DB::select(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [$table, $indexName]
        );

        return !empty($result);
    }
};
