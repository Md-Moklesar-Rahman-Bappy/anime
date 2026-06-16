<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | MAL ID
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('anime', 'mal_id')) {
                $table->unsignedBigInteger('mal_id')
                    ->nullable()
                    ->unique()
                    ->after('id');
            }

            /*
            |--------------------------------------------------------------------------
            | Sync Tracking
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('anime', 'jikan_synced_at')) {
                $table->timestamp('jikan_synced_at')
                    ->nullable()
                    ->after('banner')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {

            if (Schema::hasColumn('anime', 'mal_id')) {
                $table->dropUnique(['mal_id']);
                $table->dropColumn('mal_id');
            }

            if (Schema::hasColumn('anime', 'jikan_synced_at')) {
                $table->dropColumn('jikan_synced_at');
            }
        });
    }
};
