<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('genres', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | MAL ID (Jikan API)
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('genres', 'mal_id')) {
                $table->unsignedBigInteger('mal_id')
                    ->nullable()
                    ->after('id')
                    ->unique();
            }
        });
    }

    public function down(): void
    {
        Schema::table('genres', function (Blueprint $table) {

            if (Schema::hasColumn('genres', 'mal_id')) {
                $table->dropUnique(['mal_id']);
                $table->dropColumn('mal_id');
            }
        });
    }
};
