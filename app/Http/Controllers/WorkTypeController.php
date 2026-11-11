<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWorkTypeRequest;
use App\Http\Requests\UpdateWorkTypeRequest;
use App\Models\WorkType;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class WorkTypeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): View
    {
        $workTypes = WorkType::query()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get();

        return view('work_types.index', compact('workTypes'));
    }

    public function create(): View
    {
        return view('work_types.create');
    }

    public function store(StoreWorkTypeRequest $request): RedirectResponse
    {
        WorkType::create($request->validated());

        return redirect()->route('work-types.index')->with('success', 'تم إضافة نوع العمل بنجاح.');
    }

    public function edit(WorkType $workType): View
    {
        return view('work_types.edit', compact('workType'));
    }

    public function update(UpdateWorkTypeRequest $request, WorkType $workType): RedirectResponse
    {
        $workType->update($request->validated());

        return redirect()->route('work-types.index')->with('success', 'تم تعديل نوع العمل بنجاح.');
    }
}
