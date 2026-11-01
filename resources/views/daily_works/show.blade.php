@extends('layouts.app')

@section('title', 'تفاصيل العمل')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">تفاصيل العمل اليومي</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('daily-works.edit', $dailyWork) }}" class="btn btn-warning">تعديل</a>
            <a href="{{ route('daily-works.index') }}" class="btn btn-outline-secondary">العودة</a>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-3"><strong>رقم العقد:</strong> {{ $dailyWork->contract?->contract_number }}</div>
            <div class="col-md-3"><strong>اسم العقد:</strong> {{ $dailyWork->contract?->contract_name }}</div>
            <div class="col-md-3"><strong>تاريخ العمل:</strong> {{ $dailyWork->work_date?->format('Y-m-d') }}</div>
            <div class="col-md-3"><strong>نوع العمل:</strong> {{ $dailyWork->work_type }}</div>
            <div class="col-md-3"><strong>الوحدة:</strong> {{ $dailyWork->unit ?: 'غير محدد' }}</div>
            <div class="col-md-3"><strong>الكمية اليومية:</strong> {{ $dailyWork->daily_quantity ?? '—' }}</div>
            <div class="col-md-3"><strong>الإجمالي التراكمي:</strong> {{ number_format($cumulativeQuantity, 3) }}</div>
            <div class="col-md-12"><strong>التفاصيل:</strong> {{ $dailyWork->details ?: '—' }}</div>
        </div>
    </div>

    <div class="card p-4">
        <h4 class="mb-3">الصور المرفقة</h4>
        <div class="row g-3">
            @forelse ($dailyWork->photos as $photo)
                <div class="col-md-3">
                    <a href="{{ asset('storage/'.$photo->photo_path) }}" target="_blank">
                        <img src="{{ asset('storage/'.$photo->photo_path) }}" class="img-fluid rounded" style="height:180px;object-fit:cover;">
                    </a>
                </div>
            @empty
                <div class="col-12 text-muted">لا توجد صور مرفقة لهذا البند.</div>
            @endforelse
        </div>
    </div>
@endsection
