@extends('layouts.app')

@section('title', 'التقارير')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">التقارير والطباعة</h2>
        <a href="{{ route('reports.print', request()->query()) }}" target="_blank" class="btn btn-primary">طباعة التقرير</a>
    </div>

    <div class="card p-4 mb-4">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-3 align-items-end" data-turbo="false">
            <div class="col-md-2">
                <label class="form-label">تاريخ البداية</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">تاريخ النهاية</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label">العقد</label>
                <select name="contract_id" class="form-select">
                    <option value="">الكل</option>
                    @foreach ($contracts as $contract)
                        <option value="{{ $contract->id }}" {{ request('contract_id') == $contract->id ? 'selected' : '' }}>{{ $contract->contract_number }} - {{ $contract->contract_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">نوع العمل</label>
                <select name="work_type" class="form-select">
                    <option value="">الكل</option>
                    @foreach ($workTypeNames as $workTypeName)
                        <option value="{{ $workTypeName }}" {{ ($filters['work_type'] ?? '') === $workTypeName ? 'selected' : '' }}>{{ $workTypeName }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">بحث</button>
            </div>
        </form>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>رقم العقد</th>
                    <th>اسم العقد</th>
                    <th>نوع العمل</th>
                    <th>الوحدة</th>
                    <th>الكمية اليومية</th>
                    <th>الإجمالي التراكمي</th>
                    <th>التفاصيل</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($dailyWorks as $dailyWork)
                    <tr>
                        <td>{{ $dailyWork->work_date?->format('Y-m-d') }}</td>
                        <td>{{ $dailyWork->contract?->contract_number }}</td>
                        <td>{{ $dailyWork->contract?->contract_name }}</td>
                        <td>{{ $dailyWork->work_type }}</td>
                        <td>{{ $dailyWork->unit ?: 'غير محدد' }}</td>
                        <td>{{ $dailyWork->daily_quantity ?? '—' }}</td>
                        <td>{{ number_format($dailyWork->cumulative_quantity, 3) }}</td>
                        <td>{{ $dailyWork->details ?: '—' }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">لا توجد نتائج وفق الفلاتر الحالية.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $dailyWorks->links() }}
    </div>
@endsection
