<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropUnique(['scan_session_id', 'epc']);

            $table->unsignedInteger('ant1')->default(0)->after('antenna');
            $table->unsignedInteger('ant2')->default(0)->after('ant1');
            $table->unsignedInteger('ant3')->default(0)->after('ant2');
            $table->unsignedInteger('ant4')->default(0)->after('ant3');
        });
    }

    public function down(): void
    {
        Schema::table('tags', function (Blueprint $table) {
            $table->dropColumn(['ant1', 'ant2', 'ant3', 'ant4']);
            $table->unique(['scan_session_id', 'epc']);
        });
    }
};
