<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Source Type
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('episodes', 'source_type')) {
                $table->string('source_type')
                    ->default('upload')
                    ->after('storage_disk')
                    ->index(); // ✅ faster filtering
            }

            /*
            |--------------------------------------------------------------------------
            | Source ID (YouTube ID / Telegram ID)
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('episodes', 'source_id')) {
                $table->string('source_id')
                    ->nullable()
                    ->after('source_type')
                    ->index();
            }

            /*
            |--------------------------------------------------------------------------
            | Source URL (External / Telegram)
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('episodes', 'source_url')) {
                $table->text('source_url') // ✅ supports long URLs
                    ->nullable()
                    ->after('source_id');
            }

            /*
            |--------------------------------------------------------------------------
            | Optional Deduplication
            |--------------------------------------------------------------------------
            */
            // $table->unique(['anime_id', 'source_id']);
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {

            if (Schema::hasColumn('episodes', 'source_url')) {
                $table->dropColumn('source_url');
            }

            if (Schema::hasColumn('episodes', 'source_id')) {
                $table->dropColumn('source_id');
            }

            if (Schema::hasColumn('episodes', 'source_type')) {
                $table->dropColumn('source_type');
            }
        });
    }
};
