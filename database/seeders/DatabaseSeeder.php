<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Device;
use App\Models\Media;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. TẠO TÀI KHOẢN ADMIN (Giữ nguyên)
        User::updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Tan',
                'password' => Hash::make('12345678'),
            ]
        );

        // 2. TẠO THIẾT BỊ
        Device::updateOrCreate(
            // ['device_code' => 'DEV-L81-001'],
            [
                'name' => 'Màn hình LED Sảnh Chính',
                'ip_address' => '192.168.1.10',
                'is_active' => true,
            ]
        );

        Device::updateOrCreate(
            // ['device_code' => 'DEV-VC-002'],
            [
                'name' => 'Màn hình Thang máy 01',
                'ip_address' => '192.168.1.11',
                'is_active' => false,
            ]
        );

        // 3. TẠO MEDIA 
        Media::updateOrCreate(
            ['name' => 'Video Intro Digital Signage'],
            [
                'file_path' => 'uploads/intro.mp4',
                'file_type' => 'video',
                'file_size' => 1024576,
            ]
        );
    }
}
