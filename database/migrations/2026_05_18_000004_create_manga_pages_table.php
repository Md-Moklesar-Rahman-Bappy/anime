<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga_pages', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */
            $table->foreignId('chapter_id')
                ->constrained('chapters')
                ->cascadeOnDelete();

            /*
            |--------------------------------------------------------------------------
            | Page Info
            |--------------------------------------------------------------------------
            */
            $table->unsignedInteger('page_number'); // safer than integer
            $table->string('image_path'); // image storage path

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */
            $table->unique(['chapter_id', 'page_number']); // prevent duplicates

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */
            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | Indexes
            |--------------------------------------------------------------------------
            */
            $table->index(['chapter_id', 'page_number']); // fast loading
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga_pages');
    }
};
