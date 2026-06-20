<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'device_code',
        'address_id',
        'ip_address',
        'is_active', // Thay cho status (trạng thái online/offline)
        'last_connected_at',
    ];

    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    // Ép kiểu is_active về boolean
    protected $casts = [
        'is_active' => 'boolean',
        'last_connected_at' => 'datetime',
    ];
}
