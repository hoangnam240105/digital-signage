<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Schedule extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'days_of_week'
    ];
    // Chuyển JSON sang Mảng PHP
    protected $casts = [
        'days_of_week' => 'array',
    ];

    // Quan hệ với Media (Lịch trình này có những clip nào)
    public function media()
    {
        return $this->belongsToMany(Media::class, 'schedule_media')
            ->withPivot('zone_name', 'play_order', 'duration');
    }
    
}
