<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga_genre_relation', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */
            $table->foreignId('manga_id')
                ->constrained('manga')
                ->cascadeOnDelete()
                ->index();

            $table->foreignId('manga_genre_id')
                ->constrained('manga_genres')
                ->cascadeOnDelete()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */
            $table->primary(['manga_id', 'manga_genre_id']); // ✅ prevent duplicates

            /*
            |--------------------------------------------------------------------------
            | Optional (enable if needed)
            |--------------------------------------------------------------------------
            */
            // $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga_genre_relation');
    }
};
