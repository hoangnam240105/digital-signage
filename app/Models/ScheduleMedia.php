<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleMedia extends Model
{
    protected $table = 'schedule_media';
    protected $guarded = [];

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'media_id');
    }

    public function schedule(): BelongsTo
    {
        return $this->belongsTo(Schedule::class, 'schedule_id');
    }
}
