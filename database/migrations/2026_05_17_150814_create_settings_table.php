<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Core Key-Value
            |--------------------------------------------------------------------------
            */
            $table->string('key')->unique(); // ✅ unique already indexed
            $table->longText('value')->nullable();

            /*
            |--------------------------------------------------------------------------
            | Metadata
            |--------------------------------------------------------------------------
            */
            $table->string('type')->default('string'); // string, boolean, json
            $table->string('group')->nullable()->index(); // general, seo, etc

            /*
            |--------------------------------------------------------------------------
            | Behavior
            |--------------------------------------------------------------------------
            */
            $table->boolean('autoload')->default(true);

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
        Schema::dropIfExists('settings');
    }
};
