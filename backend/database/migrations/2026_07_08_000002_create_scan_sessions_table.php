<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scan_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reader_id')->constrained()->cascadeOnDelete();
            $table->enum('protocol', ['epc', '6b', 'gb'])->default('epc');
            $table->unsignedTinyInteger('antenna')->default(1);
            $table->boolean('read_tid')->default(false);
            $table->boolean('read_user_data')->default(false);
            $table->enum('status', ['running', 'stopped', 'error'])->default('running');
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scan_sessions');
    }
};
