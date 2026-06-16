<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chapter_bookmarks', function (Blueprint $table) {

            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index();

            $table->foreignId('chapter_id')
                ->constrained('chapters')
                ->cascadeOnDelete()
                ->index();

            $table->integer('page_number')
                ->default(1);

            $table->unique(['user_id', 'chapter_id']);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapter_bookmarks');
    }
};
