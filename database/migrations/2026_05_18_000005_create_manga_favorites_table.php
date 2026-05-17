<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga_favorites', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('manga_id')->constrained('manga')->cascadeOnDelete();
            $table->string('category')->default('plan_to_read');
            $table->timestamps();

            $table->unique(['user_id', 'manga_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga_favorites');
    }
};
