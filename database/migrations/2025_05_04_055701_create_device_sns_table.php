<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('device_sns', function (Blueprint $table) {
            $table->id('id_dsn');
            $table->string('nomor')->unique();
            $table->foreignId('model_id')->constrained('device_models', 'id_dm')->onDelete('cascade');
            $table->enum('status', ['tersedia', 'dipakai', 'rusak'])->default('tersedia');
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_sns');
    }
};
