<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manga', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Source ID
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('manga', 'source_id')) {
                $table->string('source_id')->nullable();
            }

            /*
            |--------------------------------------------------------------------------
            | Alternative Titles (optional expansion)
            |--------------------------------------------------------------------------
            */
            if (Schema::hasColumn('manga', 'alternative_titles')) {
                // ⚠ Only works if doctrine/dbal installed
                // safe to ignore if not needed
                try {
                    $table->string('alternative_titles', 1000)
                        ->nullable()
                        ->change();
                } catch (\Throwable $e) {
                    // ignore if DBAL missing
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('manga', function (Blueprint $table) {

            if (Schema::hasColumn('manga', 'source_id')) {
                $table->dropColumn('source_id');
            }
        });
    }
};
