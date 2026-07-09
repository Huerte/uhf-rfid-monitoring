<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('scan_session_id')->constrained()->cascadeOnDelete();
            $table->enum('protocol', ['epc', '6b', 'gb'])->default('epc');
            $table->string('epc')->nullable();
            $table->string('tid')->nullable();
            $table->string('user_data')->nullable();
            $table->unsignedTinyInteger('antenna')->nullable();
            $table->tinyInteger('rssi')->nullable();
            $table->timestamp('scanned_at')->useCurrent();
            $table->timestamps();

            $table->index(['scan_session_id', 'epc']);
            $table->index('scanned_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tags');
    }
};
