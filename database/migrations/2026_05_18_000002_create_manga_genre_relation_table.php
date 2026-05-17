<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga_genre_relation', function (Blueprint $table) {
            $table->foreignId('manga_id')->constrained('manga')->cascadeOnDelete();
            $table->foreignId('manga_genre_id')->constrained('manga_genres')->cascadeOnDelete();
            $table->primary(['manga_id', 'manga_genre_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga_genre_relation');
    }
};
