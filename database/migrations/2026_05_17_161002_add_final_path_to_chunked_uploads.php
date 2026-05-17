<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chunked_uploads', function (Blueprint $table) {
            $table->string('final_path')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('chunked_uploads', function (Blueprint $table) {
            $table->dropColumn('final_path');
        });
    }
};
