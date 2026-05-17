<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->integer('mal_id')->unique()->nullable()->after('id');
            $table->timestamp('jikan_synced_at')->nullable()->after('banner');
        });
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropColumn(['mal_id', 'jikan_synced_at']);
        });
    }
};
