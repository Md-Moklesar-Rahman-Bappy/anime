<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('manga', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('alternative_titles')->nullable();
            $table->string('type')->nullable();
            $table->string('status')->nullable();
            $table->integer('year')->nullable();
            $table->decimal('rating', 3, 1)->nullable();
            $table->decimal('score', 3, 1)->nullable();
            $table->integer('chapters_count')->nullable();
            $table->string('source')->nullable();
            $table->string('author')->nullable();
            $table->string('artist')->nullable();
            $table->string('publisher')->nullable();
            $table->string('thumbnail')->nullable();
            $table->string('banner')->nullable();
            $table->bigInteger('views')->default(0);
            $table->boolean('featured')->default(false);
            $table->integer('featured_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manga');
    }
};
