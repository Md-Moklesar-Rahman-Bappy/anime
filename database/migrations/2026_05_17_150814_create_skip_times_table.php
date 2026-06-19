<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skip_times', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */
            $table->foreignId('episode_id')
                ->constrained('episodes')
                ->cascadeOnDelete()
                ->unique(); // ✅ one skip config per episode

            /*
            |--------------------------------------------------------------------------
            | Skip Segments (seconds)
            |--------------------------------------------------------------------------
            */

            // Intro
            $table->unsignedInteger('intro_start')->nullable();
            $table->unsignedInteger('intro_end')->nullable();

            // Outro
            $table->unsignedInteger('outro_start')->nullable();
            $table->unsignedInteger('outro_end')->nullable();

            // Recap
            $table->unsignedInteger('recap_start')->nullable();
            $table->unsignedInteger('recap_end')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Flags
            |--------------------------------------------------------------------------
            */
            $table->boolean('has_intro')->default(false);
            $table->boolean('has_outro')->default(false);
            $table->boolean('has_recap')->default(false);

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
        Schema::dropIfExists('skip_times');
    }
};
