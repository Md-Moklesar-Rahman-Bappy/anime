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
        Schema::create('episodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anime_id')->constrained('anime')->onDelete('cascade');
            $table->integer('number');
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('video_path')->nullable();
            $table->string('storage_disk')->default('local');
            $table->integer('duration')->nullable(); // in seconds
            $table->string('thumbnail')->nullable();
            $table->boolean('has_sub')->default(true);
            $table->boolean('has_dub')->default(false);
            $table->date('air_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('episodes');
    }
};
