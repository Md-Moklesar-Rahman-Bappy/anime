<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anime', function (Blueprint $table) {
            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Core Info
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('mal_id')->nullable()->unique(); // ✅ Jikan API
            $table->string('title');
            $table->string('title_japanese')->nullable();
            $table->string('slug')->unique();

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Classification
            |--------------------------------------------------------------------------
            */
            $table->string('type')->nullable(); // TV, Movie, OVA...
            $table->string('status')->nullable()->index(); // Ongoing, Completed
            $table->string('country')->nullable();

            $table->string('season')->nullable(); // Winter, Spring...
            $table->year('year')->nullable()->index();

            /*
            |--------------------------------------------------------------------------
            | Ratings & Stats
            |--------------------------------------------------------------------------
            */
            $table->decimal('rating', 3, 1)->nullable();
            $table->decimal('score', 4, 2)->nullable()->index();

            $table->integer('episodes_count')->default(0);
            $table->integer('duration')->nullable(); // minutes

            /*
            |--------------------------------------------------------------------------
            | Production
            |--------------------------------------------------------------------------
            */
            $table->string('source')->nullable();
            $table->string('studio')->nullable();
            $table->string('producers')->nullable();
            $table->string('licensors')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Media
            |--------------------------------------------------------------------------
            */
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Analytics
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('views')->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | Features
            |--------------------------------------------------------------------------
            */
            $table->boolean('featured')->default(false);
            $table->integer('featured_order')->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | Sync Tracking
            |--------------------------------------------------------------------------
            */
            $table->timestamp('jikan_synced_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anime');
    }
};