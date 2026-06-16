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
        Schema::create('reports', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index();

            // Report target (comment / episode / anime)
            $table->morphs('reportable');
            // reportable_id + reportable_type

            /*
            |--------------------------------------------------------------------------
            | Report Data
            |--------------------------------------------------------------------------
            */

            $table->string('issue_type')->index();
            // e.g. spam, abusive, broken_video, etc.

            $table->text('description')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Moderation Status
            |--------------------------------------------------------------------------
            */

            $table->string('status')->default('pending')->index();
            // pending / resolved / rejected

            /*
            |--------------------------------------------------------------------------
            | Admin Handling
            |--------------------------------------------------------------------------
            */

            $table->foreignId('handled_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            $table->timestamp('resolved_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */

            $table->index(['reportable_id', 'reportable_type']);

            /*
            |--------------------------------------------------------------------------
            | System
            |--------------------------------------------------------------------------
            */

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
