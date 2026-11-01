<?php

namespace App\Http\Controllers;

use App\Models\Contract;
use App\Models\DailyWork;
use App\Services\DailyWorkTotalService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(protected DailyWorkTotalService $totalService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $contracts = Contract::orderBy('contract_number')->get();

        $query = DailyWork::query()->with('contract')->orderByDesc('work_date');

        if ($request->filled('contract_id')) {
            $query->where('contract_id', $request->contract_id);
        }

        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('work_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('work_date', '<=', $request->end_date);
        }

        $dailyWorks = $query->paginate(15);

        return view('reports.index', [
            'dailyWorks' => $dailyWorks,
            'contracts' => $contracts,
            'filters' => $request->only(['contract_id', 'work_type', 'start_date', 'end_date']),
        ]);
    }

    public function print(Request $request)
    {
        $query = DailyWork::query()->with('contract')->orderBy('work_date');

        if ($request->filled('contract_id')) {
            $query->where('contract_id', $request->contract_id);
        }

        if ($request->filled('work_type')) {
            $query->where('work_type', $request->work_type);
        }

        if ($request->filled('start_date')) {
            $query->whereDate('work_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('work_date', '<=', $request->end_date);
        }

        $dailyWorks = $query->get();

        return view('reports.print', [
            'dailyWorks' => $dailyWorks,
            'filters' => $request->only(['contract_id', 'work_type', 'start_date', 'end_date']),
        ]);
    }
}
