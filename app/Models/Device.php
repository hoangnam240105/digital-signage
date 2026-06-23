<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    protected $guarded = [];

    protected static ?string $recordTitleAttribute = 'name';
    
    protected $casts = [
        'last_connected_at' => 'datetime',
        'pairing_expires_at' => 'datetime',
    ];

    // Quan hệ: Một Box chỉ thuộc về một Địa chỉ/Vị trí
    public function address()
    {
        return $this->belongsTo(Address::class, 'address_id');
    }

    // Quan hệ: Một Box có nhiều lượt ghi log phát media (để sau này làm thống kê)
    public function logs()
    {
        return $this->hasMany(MediaLog::class, 'device_id');
    }
}
