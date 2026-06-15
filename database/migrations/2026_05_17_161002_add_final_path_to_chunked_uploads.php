<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('chunked_uploads', function (Blueprint $table) {

            if (!Schema::hasColumn('chunked_uploads', 'final_path')) {
                $table->string('final_path')
                    ->nullable()
                    ->after('status')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('chunked_uploads', function (Blueprint $table) {

            if (Schema::hasColumn('chunked_uploads', 'final_path')) {
                $table->dropColumn('final_path');
            }
        });
    }
};
