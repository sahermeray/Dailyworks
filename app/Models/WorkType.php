<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WorkType extends Model
{
    protected $fillable = [
        'name',
        'unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function dailyWorks(): HasMany
    {
        return $this->hasMany(DailyWork::class);
    }
}
