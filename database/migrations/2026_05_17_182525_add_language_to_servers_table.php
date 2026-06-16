<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Language (sub / dub)
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('servers', 'language')) {
                $table->string('language', 20)
                    ->default('sub') // ✅ normalized value
                    ->after('type')
                    ->index(); // ✅ improves filtering
            }
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {

            if (Schema::hasColumn('servers', 'language')) {
                $table->dropColumn('language');
            }
        });
    }
};
