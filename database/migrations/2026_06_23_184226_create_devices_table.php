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
        Schema::create('devices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->string('device_code')->unique();
            $table->foreignId('address_id')->nullable()->constrained('addresses')->onDelete('set null');
            $table->string('ip_address')->nullable();
            $table->timestamp('last_connected_at')->nullable();
            $table->string('device_token', 64)->nullable()->unique();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
