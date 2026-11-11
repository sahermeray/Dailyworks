<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyWork extends Model
{
    protected $fillable = [
        'contract_id',
        'work_type_id',
        'work_date',
        'work_type',
        'unit',
        'daily_quantity',
        'details',
    ];

    protected $casts = [
        'work_date' => 'date:Y-m-d',
        'daily_quantity' => 'decimal:3',
    ];

    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    public function workType(): BelongsTo
    {
        return $this->belongsTo(WorkType::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(DailyWorkPhoto::class);
    }
}
