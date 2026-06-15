<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chunked_uploads', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Upload Identity
            |--------------------------------------------------------------------------
            */
            $table->uuid('upload_id')
                ->unique()
                ->default(Str::uuid());

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | File Information
            |--------------------------------------------------------------------------
            */
            $table->string('filename');
            $table->string('mime_type')->nullable();

            $table->unsignedBigInteger('total_size');
            $table->integer('chunk_size');
            $table->integer('total_chunks');

            /*
            |--------------------------------------------------------------------------
            | Upload Progress
            |--------------------------------------------------------------------------
            */
            $table->integer('received_chunks')->default(0);

            /*
            |--------------------------------------------------------------------------
            | Storage
            |--------------------------------------------------------------------------
            */
            $table->text('temp_dir'); // safe for long paths
            $table->string('final_path')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */
            $table->string('status')
                ->default('uploading')
                ->index();
            // uploading / completed / failed

            /*
            |--------------------------------------------------------------------------
            | Indexing
            |--------------------------------------------------------------------------
            */
            $table->index(['user_id', 'status']);
            $table->index('created_at');

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
        Schema::dropIfExists('chunked_uploads');
    }
};