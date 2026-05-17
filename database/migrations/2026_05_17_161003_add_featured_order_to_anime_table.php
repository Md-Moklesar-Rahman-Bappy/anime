<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->integer('featured_order')->nullable()->after('featured');
        });

        DB::table('anime')->where('featured', true)->whereNull('featured_order')->update(['featured_order' => DB::raw('id')]);
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {
            $table->dropColumn('featured_order');
        });
    }
};
