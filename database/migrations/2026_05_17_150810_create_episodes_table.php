<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('episodes', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationship
            |--------------------------------------------------------------------------
            */
            $table->foreignId('anime_id')
                ->constrained('anime')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Core Info
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('number'); // ✅ safer than integer

            $table->string('title')->nullable();
            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Video / Streaming
            |--------------------------------------------------------------------------
            */
            $table->string('video_path')->nullable();
            $table->string('storage_disk')->default('local'); // ✅ FIXED (important)

            $table->string('source_type')->nullable()->index(); // ✅ youtube/telegram/upload
            $table->string('source_id')->nullable();
            $table->string('source_url')->nullable();

            $table->unsignedInteger('duration')->nullable(); // minutes

            $table->string('thumbnail')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Flags
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
            | SEO / Routing
            |--------------------------------------------------------------------------
            */
            $table->string('slug')->nullable()->index();

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
            | Sync Tracking
            |--------------------------------------------------------------------------
            */
            $table->timestamp('jikan_synced_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */
            $table->unique(['anime_id', 'number']); // ✅ no duplicate episode

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};