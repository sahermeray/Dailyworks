<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDailyWorkRequest;
use App\Http\Requests\UpdateDailyWorkRequest;
use App\Models\Contract;
use App\Models\DailyWork;
use App\Models\DailyWorkPhoto;
use App\Models\WorkType;
use App\Services\DailyWorkPhotoService;
use App\Services\DailyWorkTotalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyWorkController extends Controller
{
    public function __construct(
        protected DailyWorkPhotoService $photoService,
        protected DailyWorkTotalService $totalService,
    ) {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = $this->totalService->queryWithCumulativeQuantities()
            ->with(['contract', 'photos'])
            ->orderByDesc('work_date');

        if ($request->filled('contract_id')) {
            $query->where('contract_id', $request->input('contract_id'));
        }

        if ($request->filled('work_type')) {
            $query->where('work_type', $request->input('work_type'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('work_date', '>=', $request->input('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('work_date', '<=', $request->input('end_date'));
        }

        $dailyWorks = $query->paginate(15);
        $contracts = Contract::orderBy('contract_number')->get();
        $workTypeNames = WorkType::query()
            ->orderBy('name')
            ->pluck('name')
            ->merge(DailyWork::query()->whereNotNull('work_type')->distinct()->pluck('work_type'))
            ->unique()
            ->sort()
            ->values();

        return view('daily_works.index', compact('dailyWorks', 'contracts', 'workTypeNames'));
    }

    public function create(Request $request)
    {
        $selectedContractId = $request->query('contract_id');

        return view('daily_works.create', [
            'contracts' => Contract::orderBy('contract_number')->get(),
            'selectedContractId' => $selectedContractId,
            'workTypes' => WorkType::query()->where('is_active', true)->orderBy('name')->get(),
        ]);
    }

    public function store(StoreDailyWorkRequest $request)
    {
        $validated = $request->validated();
        $workType = WorkType::query()->whereKey($validated['work_type_id'])->where('is_active', true)->firstOrFail();
        unset($validated['work_type_id']);

        $dailyWork = DailyWork::create([
            ...$validated,
            'work_type_id' => $workType->id,
            'work_type' => $workType->name,
            'unit' => $workType->unit,
        ]);

        if ($request->hasFile('photos')) {
            $this->photoService->storePhotosForDailyWork($dailyWork->id, $request->file('photos'));
        }

        return redirect()->route('daily-works.index')->with('success', 'تم إضافة بند العمل بنجاح.');
    }

    public function show(DailyWork $dailyWork)
    {
        $dailyWork->load(['contract', 'photos']);
        $cumulativeQuantity = $this->totalService->cumulativeForContract(
            $dailyWork->contract_id,
            $dailyWork->work_type,
            $dailyWork->unit,
            $dailyWork->work_date->format('Y-m-d'),
        );

        return view('daily_works.show', compact('dailyWork', 'cumulativeQuantity'));
    }

    public function edit(DailyWork $dailyWork)
    {
        $workTypes = WorkType::query()
            ->where('is_active', true)
            ->when($dailyWork->work_type_id, fn ($query) => $query->orWhereKey($dailyWork->work_type_id))
            ->orderBy('name')
            ->get();

        return view('daily_works.edit', [
            'dailyWork' => $dailyWork->load(['contract', 'photos']),
            'contracts' => Contract::orderBy('contract_number')->get(),
            'workTypes' => $workTypes,
        ]);
    }

    public function update(UpdateDailyWorkRequest $request, DailyWork $dailyWork)
    {
        $validated = $request->validated();
        $workType = WorkType::query()->whereKey($validated['work_type_id'])->firstOrFail();
        unset($validated['work_type_id']);

        $dailyWork->update([
            ...$validated,
            'work_type_id' => $workType->id,
            'work_type' => $workType->name,
            'unit' => $workType->unit,
        ]);

        if ($request->hasFile('photos')) {
            $this->photoService->storePhotosForDailyWork($dailyWork->id, $request->file('photos'));
        }

        return redirect()->route('daily-works.index')->with('success', 'تم تعديل بند العمل بنجاح.');
    }

    public function destroy(DailyWork $dailyWork)
    {
        DB::transaction(function () use ($dailyWork) {
            $this->photoService->deletePhotosForDailyWork($dailyWork->id);
            $dailyWork->delete();
        });

        return redirect()->route('daily-works.index')->with('success', 'تم حذف بند العمل بنجاح.');
    }

    public function deletePhoto(DailyWorkPhoto $photo)
    {
        $this->photoService->deletePhoto($photo->id);

        return back()->with('success', 'تم حذف الصورة بنجاح.');
    }
}
