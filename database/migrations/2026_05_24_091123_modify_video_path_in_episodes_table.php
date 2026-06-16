<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {

            if (Schema::hasColumn('episodes', 'video_path')) {
                $table->text('video_path')
                    ->nullable()
                    ->change();
            }

            if (Schema::hasColumn('episodes', 'source_url')) {
                $table->text('source_url')
                    ->nullable()
                    ->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {

            if (Schema::hasColumn('episodes', 'video_path')) {
                $table->string('video_path')
                    ->nullable()
                    ->change();
            }

            if (Schema::hasColumn('episodes', 'source_url')) {
                $table->string('source_url')
                    ->nullable()
                    ->change();
            }
        });
    }
};