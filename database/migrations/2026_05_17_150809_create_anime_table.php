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
        Schema::create('anime', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type')->nullable(); // TV, Movie, OVA, ONA, Special
            $table->string('status')->nullable(); // Ongoing, Completed, Upcoming
            $table->string('country')->nullable();
            $table->string('season')->nullable(); // Winter, Spring, Summer, Fall
            $table->year('year')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->decimal('score', 4, 2)->nullable();
            $table->integer('episodes_count')->default(0);
            $table->integer('duration')->nullable(); // in minutes
            $table->string('source')->nullable(); // Manga, Novel, Original, etc
            $table->string('studio')->nullable();
            $table->string('producers')->nullable();
            $table->string('licensors')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();
            $table->integer('views')->default(0);
            $table->boolean('featured')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anime');
    }
};
