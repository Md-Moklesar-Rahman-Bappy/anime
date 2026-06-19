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
            | Modify video_path (string → text)
            |--------------------------------------------------------------------------
            */
            try {
                if (Schema::hasColumn('episodes', 'video_path')) {
                    $table->text('video_path')->nullable()->change();
                }
            } catch (\Throwable $e) {
                // Ignore if doctrine/dbal not installed
            }

            /*
            |--------------------------------------------------------------------------
            | Modify source_url (string → text)
            |--------------------------------------------------------------------------
            */
            try {
                if (Schema::hasColumn('episodes', 'source_url')) {
                    $table->text('source_url')->nullable()->change();
                }
            } catch (\Throwable $e) {
                // Ignore if doctrine/dbal not installed
            }
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Revert video_path to string
            |--------------------------------------------------------------------------
            */
            try {
                if (Schema::hasColumn('episodes', 'video_path')) {
                    $table->string('video_path')->nullable()->change();
                }
            } catch (\Throwable $e) {
            }

            /*
            |--------------------------------------------------------------------------
            | Revert source_url to string
            |--------------------------------------------------------------------------
            */
            try {
                if (Schema::hasColumn('episodes', 'source_url')) {
                    $table->string('source_url')->nullable()->change();
                }
            } catch (\Throwable $e) {
            }
        });
    }
};
