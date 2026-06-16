<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servers', function (Blueprint $table) {

            $table->id();

            /*
            |--------------------------------------------------------------------------
            | Relationships
            |--------------------------------------------------------------------------
            */
            $table->foreignId('episode_id')
                ->constrained()
                ->cascadeOnDelete()
                ->index();

            /*
            |--------------------------------------------------------------------------
            | Server Info
            |--------------------------------------------------------------------------
            */
            $table->string('label'); // Server 1, Vidcloud, etc
            $table->string('url');

            $table->string('type')->default('mp4');
            // mp4, m3u8, embed, youtube, telegram

            $table->string('language')->default('sub'); // ✅ sub / dub

            /*
            |--------------------------------------------------------------------------
            | Priority (IMPORTANT)
            |--------------------------------------------------------------------------
            */
            $table->integer('priority')->default(0)->index();

            /*
            |--------------------------------------------------------------------------
            | Constraints
            |--------------------------------------------------------------------------
            */
            $table->unique(['episode_id', 'url']); // ✅ prevent duplicates

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servers');
    }
};
