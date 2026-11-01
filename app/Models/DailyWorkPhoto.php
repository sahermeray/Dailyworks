<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyWorkPhoto extends Model
{
    protected $fillable = [
        'daily_work_id',
        'photo_path',
        'original_file_name',
    ];

    public function dailyWork(): BelongsTo
    {
        return $this->belongsTo(DailyWork::class);
    }
}
