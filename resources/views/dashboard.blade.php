@extends('layouts.app')

@section('title', 'لوحة التحكم')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">لوحة التحكم</h2>
        <a href="{{ route('contracts.create') }}" class="btn btn-primary">إضافة عقد جديد</a>
    </div>

    <div class="row g-3">
        <div class="col-md-3">
            <div class="card p-3 h-100">
                <div class="text-muted">عدد العقود</div>
                <div class="display-6 fw-bold">{{ $contractsCount }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 h-100">
                <div class="text-muted">عدد الأعمال اليومية المسجلة</div>
                <div class="display-6 fw-bold">{{ $dailyWorksCount }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 h-100">
                <div class="text-muted">أعمال الشهر الحالي</div>
                <div class="display-6 fw-bold">{{ $currentMonthWorks }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card p-3 h-100">
                <div class="text-muted">الأنواع المسجلة</div>
                <div class="display-6 fw-bold">{{ $totalsByType->count() }}</div>
            </div>
        </div>
    </div>

    <div class="card mt-4 p-4">
        <h4 class="mb-3">الإجمالي الكمي بحسب نوع العمل ووحدته</h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>نوع العمل</th>
                    <th>الوحدة</th>
                    <th>الإجمالي</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($totalsByType as $total)
                    <tr>
                        <td>{{ $total->work_type }}</td>
                        <td>{{ $total->unit ?: 'غير محدد' }}</td>
                        <td>{{ number_format((float) $total->total_quantity, 3) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="text-center text-muted">لا توجد بيانات بعد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
