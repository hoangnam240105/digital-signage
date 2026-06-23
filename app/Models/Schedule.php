<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
    public function device()
    {
        return $this->belongsTo(\App\Models\Device::class, 'device_id');
    }

    public function media()
    {
        return $this->belongsToMany(\App\Models\Media::class, 'schedule_media', 'schedule_id', 'media_id');
    }
    public function addresses()
    {
        return $this->belongsToMany(Address::class, 'address_schedule', 'schedule_id', 'address_id');
    }
    public function scheduleMedia(): HasMany
    {
        return $this->hasMany(ScheduleMedia::class, 'schedule_id');
    }
}
