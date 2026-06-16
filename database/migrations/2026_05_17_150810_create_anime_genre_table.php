<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('anime_genre', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Foreign Keys
            |--------------------------------------------------------------------------
            */
            $table->foreignId('anime_id')
                ->constrained('anime')
                ->cascadeOnDelete();

            $table->foreignId('genre_id')
                ->constrained('genres')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Composite Primary Key
            |--------------------------------------------------------------------------
            */
            $table->primary(['anime_id', 'genre_id']);

            /*
            |--------------------------------------------------------------------------
            | Indexes (IMPORTANT FOR PERFORMANCE)
            |--------------------------------------------------------------------------
            */
            $table->index('anime_id');
            $table->index('genre_id');

            /*
            |--------------------------------------------------------------------------
            | Optional (enable if using withTimestamps)
            |--------------------------------------------------------------------------
            */
            // $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime_genre');
    }
};