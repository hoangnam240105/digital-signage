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
        Schema::create('media_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('device_id')->comment('ID của thiết bị/box phát');
            $table->unsignedBigInteger('media_id')->comment('ID của file media/video');
            $table->unsignedBigInteger('schedule_id')->nullable()->comment('ID của lịch trình chiếu');
            $table->dateTime('played_at')->comment('Thời gian thực tế video phát xong');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('media_logs');
    }
};
