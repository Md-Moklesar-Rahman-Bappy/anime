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

            // ✅ stores serialized cache data
            $table->mediumText('value');

            // ✅ UNIX timestamp for expiration
            $table->integer('expiration')->index();
        });

        /*
        |--------------------------------------------------------------------------
        | Cache Locks Table
        |--------------------------------------------------------------------------
        */
        Schema::create('cache_locks', function (Blueprint $table) {
            $table->string('key')->primary();

            // ✅ lock owner (request/process id)
            $table->string('owner');

            // ✅ expiration timestamp
            $table->integer('expiration')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // ✅ drop locks first (dependency safety)
        Schema::dropIfExists('cache_locks');
        Schema::dropIfExists('cache');
    }
};