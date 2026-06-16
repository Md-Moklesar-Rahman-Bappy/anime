<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('favorites', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Category (Optional grouping)
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('favorites', 'category')) {
                $table->string('category', 50) // allow slightly larger values
                    ->nullable()
                    ->after('anime_id')
                    ->index(); // ✅ useful for filtering
            }
        });
    }

    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {

            if (Schema::hasColumn('favorites', 'category')) {
                $table->dropColumn('category');
            }
        });
    }
};