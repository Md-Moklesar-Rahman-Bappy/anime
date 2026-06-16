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
        Schema::create('comments', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('episode_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Nested Comments (Replies)
            |--------------------------------------------------------------------------
            */

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('comments')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Core Content
            |--------------------------------------------------------------------------
            */

            $table->text('body');

            /*
            |--------------------------------------------------------------------------
            | Engagement
            |--------------------------------------------------------------------------
            */

            $table->unsignedInteger('likes_count')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Moderation
            |--------------------------------------------------------------------------
            */

            $table->string('status')->default('visible')->index();
            // visible / hidden / deleted

            /*
            |--------------------------------------------------------------------------
            | Indexing
            |--------------------------------------------------------------------------
            */

            $table->index(['episode_id', 'created_at']);

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
            $table->softDeletes(); // ✅ safer deletion
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};