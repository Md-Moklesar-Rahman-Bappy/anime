<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->index('views', 'anime_views_index');
            $table->index('created_at', 'anime_created_at_index');
            $table->index(['featured', 'featured_order'], 'anime_featured_order_index');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->index('created_at', 'episodes_created_at_index');
            $table->index(['anime_id', 'number'], 'episodes_anime_id_number_index');
        });

        Schema::table('manga', function (Blueprint $table) {
            $table->index('views', 'manga_views_index');
            $table->index('created_at', 'manga_created_at_index');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->index('created_at', 'chapters_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropIndex('anime_views_index');
            $table->dropIndex('anime_created_at_index');
            $table->dropIndex('anime_featured_order_index');
        });

        Schema::table('episodes', function (Blueprint $table) {
            $table->dropIndex('episodes_created_at_index');
            $table->dropIndex('episodes_anime_id_number_index');
        });

        Schema::table('manga', function (Blueprint $table) {
            $table->dropIndex('manga_views_index');
            $table->dropIndex('manga_created_at_index');
        });

        Schema::table('chapters', function (Blueprint $table) {
            $table->dropIndex('chapters_created_at_index');
        });
    }
};
