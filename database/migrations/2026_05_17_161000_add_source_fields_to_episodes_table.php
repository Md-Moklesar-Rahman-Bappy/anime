<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->string('source_type')->default('upload')->after('storage_disk');
            $table->string('source_id')->nullable()->after('source_type');
            $table->string('source_url')->nullable()->after('source_id');
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {
            $table->dropColumn(['source_type', 'source_id', 'source_url']);
        });
    }
};
