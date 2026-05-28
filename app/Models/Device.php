<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'location',
        'ip_address',
        'is_active',
        'last_connected_at',
    ];

    // Nếu bạn muốn Laravel tự động ép kiểu is_active về boolean
    protected $casts = [
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
    ];
}
