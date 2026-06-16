<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Core Fields
            |--------------------------------------------------------------------------
            */
            $table->id();

            $table->foreignId('anime_id')
                ->constrained('anime')
                ->cascadeOnDelete()
                ->index();

            $table->integer('number');

            $table->string('title')->nullable();
            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Streaming
            |--------------------------------------------------------------------------
            */
            $table->string('video_path')->nullable();
            $table->string('storage_disk')->default('public'); // ✅ better default

            $table->integer('duration')->nullable(); // in minutes

            $table->string('thumbnail')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Features
            |--------------------------------------------------------------------------
            */
            $table->boolean('has_sub')->default(true);
            $table->boolean('has_dub')->default(false);

            $table->date('air_date')->nullable()->index();

            /*
            |--------------------------------------------------------------------------
            | Analytics
            |--------------------------------------------------------------------------
            */
            $table->unsignedBigInteger('views')->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */
            $table->string('slug')->nullable()->index(); // ✅ SEO

            /*
            |--------------------------------------------------------------------------
            | Ownership
            |--------------------------------------------------------------------------
            */
            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Sync tracking
            |--------------------------------------------------------------------------
            */
            $table->timestamp('jikan_synced_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */
            $table->unique(['anime_id', 'number']); // ✅ prevents duplicates

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};