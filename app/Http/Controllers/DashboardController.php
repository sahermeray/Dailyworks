<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\DailyWork;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $contractsCount = Contract::count();
        $dailyWorksCount = DailyWork::count();
        $currentMonthWorks = DailyWork::whereMonth('work_date', now()->month)
            ->whereYear('work_date', now()->year)
            ->count();

        $totalsByType = DailyWork::query()
            ->select('work_type', 'unit', DB::raw('SUM(daily_quantity) as total_quantity'))
            ->groupBy('work_type', 'unit')
            ->orderBy('work_type')
            ->get();

        return view('dashboard', compact(
            'contractsCount',
            'dailyWorksCount',
            'currentMonthWorks',
            'totalsByType',
        ));
    }
}
