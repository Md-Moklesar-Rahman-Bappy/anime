<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servers', function (Blueprint $table) {

            if (Schema::hasColumn('servers', 'url')) {
                $table->text('url')
                    ->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {

            if (Schema::hasColumn('servers', 'url')) {
                $table->string('url', 255)
                    ->change();
            }
        });
    }
};
