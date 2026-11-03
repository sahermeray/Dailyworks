<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DailyWork extends Model
{
    public const WORK_TYPE_UNITS = [
        'قشط الزفت المهترئ ٥سم' => 'متر مربع',
        'قشط الزفت المهترئ ١١سم' => 'متر مربع',
        'تقديم ومد ودحي مجبول اسفلتي اهتراء سماكة ٥ سم' => 'متر مكعب',
        'تقديم ومد ودحي مجبول اسفلتي اساس سماكة ٦سم' => 'متر مكعب',
        'تقديم ومد ودحي مجبول اسفلتي اساس سماكة ٧سم' => 'متر مكعب',
        'تقديم ومد ودحي مجبول اسفلتي بمجابل حرارية' => 'متر مكعب',
        'قص وترقيع بالمجبول الاسفلتي' => 'متر مكعب',
        'تقديم ورش مانع اسفلتي (ام سي زيرو) لزوم طبقة لاصقة' => 'متر مربع',
        'طبقة تشرب(ام سي زيرو)' => 'متر مربع',
        'ترحيل' => 'متر مكعب',
        'تسوية بالمجبول الاسفلتي' => 'متر مكعب',
        'تسوية جوانب الطريق بالكريدر' => 'متر مربع',
        'استبدال طبقات اساس بحص مكسر' => 'متر مكعب',
        'استبدال طبقات ما تحت اساس بحص مكسر' => 'متر مكعب',
        'استبدال وصيانة حواجز بيتونية مكسرة' => 'متر طولي',
    ];

    public const UNITS = ['متر', 'متر مربع', 'متر مكعب', 'متر طولي'];

    protected $fillable = [
        'contract_id',
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

    public function photos(): HasMany
    {
        return $this->hasMany(DailyWorkPhoto::class);
    }
}
