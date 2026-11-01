@extends('layouts.app')

@section('title', 'تفاصيل العقد')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">تفاصيل العقد</h2>
        <div class="d-flex gap-2">
            <a href="{{ route('contracts.edit', $contract) }}" class="btn btn-warning">تعديل</a>
            <a href="{{ route('daily-works.create', ['contract_id' => $contract->id]) }}" class="btn btn-primary">إضافة عمل يومي</a>
        </div>
    </div>

    <div class="card p-4 mb-4">
        <div class="row g-3">
            <div class="col-md-3"><strong>رقم العقد:</strong> {{ $contract->contract_number }}</div>
            <div class="col-md-3"><strong>اسم العقد:</strong> {{ $contract->contract_name }}</div>
            <div class="col-md-3"><strong>اسم المشرف:</strong> {{ $contract->supervisor_name }}</div>
            <div class="col-md-3"><strong>الشركة المنفذة:</strong> {{ $contract->executing_company }}</div>
            <div class="col-md-6"><strong>تاريخ الإسناد:</strong> {{ $contract->assignment_date?->format('Y-m-d') }}</div>
            <div class="col-md-6"><strong>أعضاء الإشراف:</strong>
                @forelse ($contract->supervisoryMembers as $member)
                    <span class="badge-soft me-1">{{ $member->member_name }}</span>
                @empty
                    <span class="text-muted">لا يوجد</span>
                @endforelse
            </div>
        </div>
    </div>

    <div class="card p-4">
        <h4 class="mb-3">الأعمال اليومية المرتبطة بالعقد</h4>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>نوع العمل</th>
                    <th>الوحدة</th>
                    <th>الكمية اليومية</th>
                    <th>التفاصيل</th>
                    <th>الصور</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($contract->dailyWorks as $dailyWork)
                    <tr>
                        <td>{{ $dailyWork->work_date?->format('Y-m-d') }}</td>
                        <td>{{ $dailyWork->work_type }}</td>
                        <td>{{ $dailyWork->unit ?: 'غير محدد' }}</td>
                        <td>{{ $dailyWork->daily_quantity ?? '—' }}</td>
                        <td>{{ $dailyWork->details ?? '—' }}</td>
                        <td>
                            @foreach ($dailyWork->photos as $photo)
                                <a href="{{ asset('storage/'.$photo->photo_path) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$photo->photo_path) }}" style="width:60px;height:60px;object-fit:cover;border-radius:8px;" alt="صورة">
                                </a>
                            @endforeach
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">لا توجد أعمال مسجلة لهذا العقد.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
