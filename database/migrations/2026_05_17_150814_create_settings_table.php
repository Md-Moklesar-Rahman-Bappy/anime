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
        Schema::create('settings', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Core Key-Value
            |--------------------------------------------------------------------------
            */
            $table->string('key')->unique()->index();

            $table->longText('value')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */
            $table->string('type')->default('string');
            // string, boolean, json, number

            $table->string('group')->nullable()->index();
            // general, seo, branding, streaming

            /*
            |--------------------------------------------------------------------------
            | Behavior
            |--------------------------------------------------------------------------
            */
            $table->boolean('autoload')->default(true);
            // load automatically in AppLayout

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
        Schema::dropIfExists('settings');
    }
};
