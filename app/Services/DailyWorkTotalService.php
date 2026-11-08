<?php

namespace App\Services;

use App\Models\DailyWork;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class DailyWorkTotalService
{
    public function queryWithCumulativeQuantities(): Builder
    {
        $dailyWorksWithCumulativeQuantities = DailyWork::query()
            ->select('daily_works.*')
            ->selectRaw(
                'CASE WHEN unit IS NULL OR unit = ? THEN '
                    .'SUM(daily_quantity) OVER (PARTITION BY contract_id, work_type ORDER BY work_date) '
                    .'ELSE SUM(daily_quantity) OVER (PARTITION BY contract_id, work_type, unit ORDER BY work_date) '
                    .'END AS cumulative_quantity',
                [''],
            );

        $query = DailyWork::query();
        $query->getQuery()->fromSub($dailyWorksWithCumulativeQuantities->toBase(), 'daily_works');

        return $query;
    }

    /**
     * @return Collection<int, object>
     */
    public function totalsForContract(int $contractId): Collection
    {
        return DailyWork::query()
            ->where('contract_id', $contractId)
            ->select('work_type', 'unit', DB::raw('SUM(daily_quantity) as total_quantity'))
            ->groupBy('work_type', 'unit')
            ->get();
    }

    public function cumulativeForContract(int $contractId, string $workType, ?string $unit = null, ?string $asOfDate = null): float
    {
        $query = DailyWork::query()
            ->where('contract_id', $contractId)
            ->where('work_type', $workType)
            ->whereDate('work_date', '<=', $asOfDate ?? now()->toDateString());

        if ($unit !== null && $unit !== '') {
            $query->where('unit', $unit);
        }

        return (float) $query->sum('daily_quantity');
    }

    /**
     * @return Collection<int, object>
     */
    public function totalsForPeriod(?int $contractId = null, ?string $startDate = null, ?string $endDate = null): Collection
    {
        $query = DailyWork::query()
            ->with('contract')
            ->when($contractId, fn ($builder) => $builder->where('contract_id', $contractId))
            ->when($startDate, fn ($builder) => $builder->whereDate('work_date', '>=', $startDate))
            ->when($endDate, fn ($builder) => $builder->whereDate('work_date', '<=', $endDate))
            ->select('daily_works.*');

        return $query->get();
    }
}
