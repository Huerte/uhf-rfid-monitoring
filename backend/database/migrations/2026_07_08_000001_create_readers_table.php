<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('readers', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('G-Series Reader');
            $table->string('ip', 45)->default('192.168.1.168');
            $table->unsignedSmallInteger('port')->default(8160);
            $table->boolean('connected')->default(false);
            $table->timestamp('connected_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('readers');
    }
};
