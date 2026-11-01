<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreContractRequest;
use App\Http\Requests\UpdateContractRequest;
use App\Models\Contract;
use App\Services\DailyWorkPhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ContractController extends Controller
{
    public function __construct(protected DailyWorkPhotoService $photoService)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $query = Contract::query()->with('supervisoryMembers')->orderByDesc('assignment_date');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($builder) use ($search) {
                $builder->where('contract_number', 'like', "%{$search}%")
                    ->orWhere('contract_name', 'like', "%{$search}%");
            });
        }

        $contracts = $query->paginate(12);

        return view('contracts.index', compact('contracts'));
    }

    public function create()
    {
        return view('contracts.create');
    }

    public function store(StoreContractRequest $request)
    {
        $contract = Contract::create($request->validated());

        foreach ($request->input('supervisory_members', []) as $memberName) {
            $trimmedMember = trim((string) $memberName);

            if ($trimmedMember !== '') {
                $contract->supervisoryMembers()->create([
                    'member_name' => $trimmedMember,
                ]);
            }
        }

        return redirect()->route('contracts.index')->with('success', 'تم إضافة العقد بنجاح.');
    }

    public function show(Contract $contract)
    {
        $contract->load(['supervisoryMembers', 'dailyWorks' => fn ($query) => $query->orderBy('work_date', 'desc')]);

        return view('contracts.show', compact('contract'));
    }

    public function edit(Contract $contract)
    {
        $contract->load('supervisoryMembers');

        return view('contracts.edit', compact('contract'));
    }

    public function update(UpdateContractRequest $request, Contract $contract)
    {
        $contract->update($request->validated());

        $contract->supervisoryMembers()->delete();

        foreach ($request->input('supervisory_members', []) as $memberName) {
            $trimmedMember = trim((string) $memberName);

            if ($trimmedMember !== '') {
                $contract->supervisoryMembers()->create([
                    'member_name' => $trimmedMember,
                ]);
            }
        }

        return redirect()->route('contracts.show', $contract)->with('success', 'تم تعديل العقد بنجاح.');
    }

    public function destroy(Contract $contract)
    {
        DB::transaction(function () use ($contract) {
            foreach ($contract->dailyWorks()->with('photos')->get() as $dailyWork) {
                $this->photoService->deletePhotosForDailyWork($dailyWork->id);
                $dailyWork->delete();
            }

            $contract->supervisoryMembers()->delete();
            $contract->delete();
        });

        return redirect()->route('contracts.index')->with('success', 'تم حذف العقد وجميع أعماله وصوره نهائياً.');
    }
}
