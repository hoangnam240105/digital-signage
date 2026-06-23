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
        Schema::create('schedule_media', function (Blueprint $table) {
            $table->id();

            // Khóa ngoại liên kết tới bảng schedules
            $table->foreignId('schedule_id')
                ->constrained()
                ->onDelete('cascade');

            // Khóa ngoại liên kết tới bảng media (ĐÃ ĐỂ NULLABLE ĐỂ FIX LỖI FILAMENT)
            $table->foreignId('media_id')
                ->nullable()
                ->constrained('media')
                ->onDelete('cascade');

            // Các cột thông tin cấu hình phát
            $table->string('zone_name')->nullable();
            $table->integer('play_order')->nullable();
            $table->integer('duration')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('schedule_media');
    }
};
