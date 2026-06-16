<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('episodes', function (Blueprint $table) {

            if (!Schema::hasColumn('episodes', 'telegram_message_id')) {
                $table->unsignedBigInteger('telegram_message_id')
                    ->nullable()
                    ->after('source_url')
                    ->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('episodes', function (Blueprint $table) {

            if (Schema::hasColumn('episodes', 'telegram_message_id')) {
                $table->dropColumn('telegram_message_id');
            }
        });
    }
};
