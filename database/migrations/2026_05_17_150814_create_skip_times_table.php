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
        Schema::create('skip_times', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */
            $table->foreignId('episode_id')
                ->constrained()
                ->cascadeOnDelete()
                ->unique(); // ✅ one skip config per episode

            /*
            |--------------------------------------------------------------------------
            | Skip Segments
            |--------------------------------------------------------------------------
            */

            // Intro skip
            $table->unsignedInteger('intro_start')->nullable();
            $table->unsignedInteger('intro_end')->nullable();

            // Outro skip
            $table->unsignedInteger('outro_start')->nullable();
            $table->unsignedInteger('outro_end')->nullable();

            // Optional extra (recap)
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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('skip_times');
    }
};
