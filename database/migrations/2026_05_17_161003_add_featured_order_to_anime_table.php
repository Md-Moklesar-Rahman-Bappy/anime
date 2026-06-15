<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('anime', function (Blueprint $table) {

            /*
            |--------------------------------------------------------------------------
            | Featured Order
            |--------------------------------------------------------------------------
            */
            if (!Schema::hasColumn('anime', 'featured_order')) {
                $table->integer('featured_order')
                    ->nullable()
                    ->after('featured')
                    ->index();
            }
        });

        /*
        |--------------------------------------------------------------------------
        | Initialize order for existing featured items
        |--------------------------------------------------------------------------
        */
        DB::table('anime')
            ->where('featured', true)
            ->whereNull('featured_order')
            ->update([
                'featured_order' => DB::raw('id')
            ]);
    }

    public function down(): void
    {
        Schema::table('anime', function (Blueprint $table) {

            if (Schema::hasColumn('anime', 'featured_order')) {
                $table->dropColumn('featured_order');
            }
        });
    }
};
