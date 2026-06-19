<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | External API / Sync
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('mal_id')
                ->nullable()
                ->unique()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Core Info
            |--------------------------------------------------------------------------
            */
            $table->string('title')->index();
            $table->string('slug')->unique()->index();

            $table->text('description')->nullable();
            $table->string('alternative_titles')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Classification
            |--------------------------------------------------------------------------
            */
            $table->string('type')->nullable()->index(); // manga, manhwa, manhua
            $table->string('status')->nullable()->index(); // ongoing, completed
            $table->year('year')->nullable()->index();

            /*
            |--------------------------------------------------------------------------
            | Ratings
            |--------------------------------------------------------------------------
            */
            $table->decimal('rating', 3, 1)->nullable();
            $table->decimal('score', 4, 2)->nullable()->index();

            /*
            |--------------------------------------------------------------------------
            | Content Stats
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('chapters_count')->default(0);
            $table->unsignedBigInteger('views')->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | Production
            |--------------------------------------------------------------------------
            */
            $table->string('source')->nullable();
            $table->string('author')->nullable();
            $table->string('artist')->nullable();
            $table->string('publisher')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Media
            |--------------------------------------------------------------------------
            */
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Features
            |--------------------------------------------------------------------------
            */
            $table->boolean('featured')->default(false)->index();
            $table->unsignedInteger('featured_order')->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | Sync Tracking
            |--------------------------------------------------------------------------
            */
            $table->timestamp('jikan_synced_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga');
    }
};
