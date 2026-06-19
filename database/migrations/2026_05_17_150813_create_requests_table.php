<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anime_requests', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Request Data
            |--------------------------------------------------------------------------
            */
            $table->string('anime_title')->index();
            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Moderation
            |--------------------------------------------------------------------------
            */
            $table->string('status')
                ->default('pending')
                ->index();
            // pending / fulfilled / rejected

            /*
            |--------------------------------------------------------------------------
            | Admin Handling
            |--------------------------------------------------------------------------
            */
            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Linked Anime
            |--------------------------------------------------------------------------
            */
            $table->foreignId('anime_id')
                ->nullable()
                ->constrained('anime')
                ->nullOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */
            $table->unique(['user_id', 'anime_title']); // ✅ prevent duplicates

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
        Schema::dropIfExists('anime_requests');
    }
};
