<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manga', function (Blueprint $table) {
            $table->string('source_id')->nullable()->after('source');
            $table->string('alternative_titles', 1000)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('manga', function (Blueprint $table) {
            $table->dropColumn('source_id');
        });
    }
};
