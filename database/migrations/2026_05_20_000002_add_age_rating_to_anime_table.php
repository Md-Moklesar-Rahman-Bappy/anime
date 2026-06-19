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
            | Age Rating (PG-13, R, R+, etc.)
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('anime', 'age_rating')) {
                $table->string('age_rating', 20)->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {

            if (Schema::hasColumn('anime', 'age_rating')) {
                $table->dropColumn('age_rating');
            }
        });
    }
};