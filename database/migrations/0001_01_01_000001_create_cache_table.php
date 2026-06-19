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
        | Cache Table
        |--------------------------------------------------------------------------
        */
        Schema::create('cache', function (Blueprint $table) {
            $table->string('key')->primary();

            // Stores serialized cache values
            $table->mediumText('value');

            // UNIX timestamp expiration
            $table->integer('expiration')->index();
        });

        /*
        |--------------------------------------------------------------------------
        | Cache Locks Table
        |--------------------------------------------------------------------------
        */
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();

            // Lock owner (process/job id)
            $table->string('owner');

            // Expiration timestamp
            $table->integer('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};
