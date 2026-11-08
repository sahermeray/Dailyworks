@extends('layouts.app')

@section('title', 'إدارة العقود')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">إدارة العقود</h2>
        <a href="{{ route('contracts.create') }}" class="btn btn-primary">إضافة عقد جديد</a>
    </div>

    <div class="card p-4 mb-4">
        <form method="GET" action="{{ route('contracts.index') }}" class="row g-2 align-items-end" data-turbo="false">
            <div class="col-md-10">
                <label class="form-label">بحث سريع</label>
                <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="رقم العقد أو اسمه">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-outline-primary w-100">بحث</button>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>رقم العقد</th>
                    <th>اسم العقد</th>
                    <th>اسم المشرف</th>
                    <th>الشركة المنفذة</th>
                    <th>تاريخ الإسناد</th>
                    <th>أعضاء الإشراف</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($contracts as $contract)
                    <tr>
                        <td>{{ $contract->contract_number }}</td>
                        <td>{{ $contract->contract_name }}</td>
                        <td>{{ $contract->supervisor_name }}</td>
                        <td>{{ $contract->executing_company }}</td>
                        <td>{{ $contract->assignment_date?->format('Y-m-d') }}</td>
                        <td>
                            @forelse ($contract->supervisoryMembers as $member)
                                <span class="badge-soft d-inline-block mb-1">{{ $member->member_name }}</span>
                            @empty
                                <span class="text-muted">لا يوجد</span>
                            @endforelse
                        </td>
                        <td>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="{{ route('contracts.show', $contract) }}" class="btn btn-sm btn-info text-white">عرض</a>
                                <a href="{{ route('contracts.edit', $contract) }}" class="btn btn-sm btn-warning">تعديل</a>
                                <form action="{{ route('contracts.destroy', $contract) }}" method="POST" data-turbo="false" onsubmit="return confirm('هل أنت متأكد؟ سيتم حذف جميع الأعمال والصور التابعة لهذا العقد بشكل نهائي.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted">لا توجد عقود مسجلة.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{ $contracts->links() }}
    </div>
@endsection
