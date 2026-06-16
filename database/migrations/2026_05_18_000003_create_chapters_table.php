<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapters', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */
            $table->foreignId('manga_id')
                ->constrained('manga')
                ->cascadeOnDelete()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Chapter Info
            |--------------------------------------------------------------------------
            */
            $table->decimal('number', 8, 2); // ✅ supports decimals like 1.5
            $table->string('title')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Content Data
            |--------------------------------------------------------------------------
            */
            $table->integer('pages_count')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */
            $table->unique(['manga_id', 'number']); // ✅ prevent duplicate chapters

            /*
            |--------------------------------------------------------------------------
            | Indexing
            |--------------------------------------------------------------------------
            */
            $table->index(['manga_id', 'created_at']); // ✅ faster listing

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
        Schema::dropIfExists('chapters');
    }
};
