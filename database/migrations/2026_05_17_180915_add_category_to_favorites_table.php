<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add category column only if it doesn't already exist
        if (!Schema::hasColumn('favorites', 'category')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->string('category', 50)
                    ->nullable()
                    ->index(); // for filtering
            });
        }
    }

    public function down(): void
    {
        // Drop column safely
        if (Schema::hasColumn('favorites', 'category')) {
            Schema::table('favorites', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }
};
