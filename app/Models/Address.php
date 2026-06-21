<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'description'];
    public function devices()
    {
        return $this->hasMany(Device::class, 'address_id');
    }
    
    public function schedules()
    {
        return $this->belongsToMany(Schedule::class, 'address_schedule', 'address_id', 'schedule_id');
    }
}
