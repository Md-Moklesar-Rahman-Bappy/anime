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
            $table->foreignId('manga_id')->constrained('manga')->cascadeOnDelete();
            $table->decimal('number', 8, 1);
            $table->string('title')->nullable();
            $table->integer('pages_count')->default(0);
            $table->timestamps();

            $table->unique(['manga_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chapters');
    }
};
