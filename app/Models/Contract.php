<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Contract extends Model
{
    protected $fillable = [
        'contract_number',
        'contract_name',
        'supervisor_name',
        'executing_company',
        'assignment_date',
    ];

    protected $casts = [
        'assignment_date' => 'date:Y-m-d',
    ];

    public function supervisoryMembers(): HasMany
    {
        return $this->hasMany(ContractSupervisoryMember::class);
    }

    public function dailyWorks(): HasMany
    {
        return $this->hasMany(DailyWork::class);
    }
}
