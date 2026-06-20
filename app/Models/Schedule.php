<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;

    // Lệnh này mở khóa toàn bộ, cho phép lưu mọi cột từ Form
    protected $guarded = [];

    // Tự động ép kiểu dữ liệu
    protected $casts = [
        'days_of_week' => 'array',
        'is_active' => 'boolean',
    ];

    // Quan hệ với Media
    public function media()
    {
        return $this->belongsToMany(Media::class, 'schedule_media', 'schedule_id', 'media_id')
            ->withPivot('zone_name', 'play_order', 'duration');
    }
}