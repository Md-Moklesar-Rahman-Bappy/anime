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
        /*
        |--------------------------------------------------------------------------
        | Jobs Table
        |--------------------------------------------------------------------------
        */
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();

            // Queue name (important for multi-queue setup)
            $table->string('queue')->index();

            // Serialized job payload
            $table->longText('payload');

            // Retry attempts
            $table->unsignedTinyInteger('attempts');

            // When worker reserved job
            $table->unsignedInteger('reserved_at')->nullable()->index();

            // When job becomes available
            $table->unsignedInteger('available_at')->index();

            // Unix timestamp
            $table->unsignedInteger('created_at')->index();
        });

        /*
        |--------------------------------------------------------------------------
        | Job Batches (Batch Processing)
        |--------------------------------------------------------------------------
        */
        Schema::create('job_batches', function (Blueprint $table) {
            $table->string('id')->primary();

            $table->string('name');

            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');

            // JSON-like storage
            $table->longText('failed_job_ids');

            $table->mediumText('options')->nullable();

            $table->integer('cancelled_at')->nullable()->index();
            $table->integer('created_at')->index();
            $table->integer('finished_at')->nullable()->index();
        });

        /*
        |--------------------------------------------------------------------------
        | Failed Jobs Table
        |--------------------------------------------------------------------------
        */
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();

            $table->string('uuid')->unique();

            $table->text('connection');
            $table->text('queue');

            $table->longText('payload');
            $table->longText('exception');

            $table->timestamp('failed_at')->useCurrent()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ✅ safe drop order
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
    }
};
