<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga_comments', function (Blueprint $table) {

            $table->id();

            $table->foreignId('chapter_id')
                ->constrained('chapters')
                ->cascadeOnDelete()
                ->index();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index();

            $table->foreignId('parent_id')
                ->nullable()
                ->constrained('manga_comments')
                ->cascadeOnDelete();

            $table->text('body');

            $table->unsignedInteger('likes_count')
                ->default(0);

            $table->string('status')
                ->default('visible')
                ->index();

            $table->index(['chapter_id', 'created_at']);

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga_comments');
    }
};
