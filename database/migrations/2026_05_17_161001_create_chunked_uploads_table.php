<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chunked_uploads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('filename');
            $table->string('mime_type')->nullable();
            $table->bigInteger('total_size');
            $table->integer('chunk_size');
            $table->integer('total_chunks');
            $table->integer('received_chunks')->default(0);
            $table->string('temp_dir');
            $table->string('status')->default('uploading');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chunked_uploads');
    }
};
