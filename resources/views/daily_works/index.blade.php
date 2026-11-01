@extends('layouts.app')

@section('title', 'الأعمال اليومية')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">الأعمال اليومية</h2>
        <a href="{{ route('daily-works.create') }}" class="btn btn-primary">إضافة بند عمل</a>
    </div>

    <div class="card p-4 mb-4">
        <form method="GET" action="{{ route('daily-works.index') }}" class="row g-3 align-items-end">
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
                    @foreach (['قشط الزفت المهترئ','تقديم ورش مانع اسفلتي MC0','ترحيل أتربة','تقديم وتنفيذ طبقة مجبول أسفلتي','تقديم ومد ودحي مجبول أسفلتي','قص وترقيع بالمجبول الأسفلتي','مد قميص أسفلتي'] as $type)
                        <option value="{{ $type }}" {{ request('work_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">من تاريخ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <label class="form-label">إلى تاريخ</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">بحث</button>
            </div>
        </form>
    </div>

    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>التاريخ</th>
                    <th>رقم العقد</th>
                    <th>اسم العقد</th>
                    <th>العمل</th>
                    <th>الوحدة</th>
                    <th>الكمية اليومية</th>
                    <th>الإجمالي التراكمي</th>
                    <th>التفاصيل</th>
                    <th>الصور</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($dailyWorks as $dailyWork)
                    @php
                        $total = app(App\Services\DailyWorkTotalService::class)->cumulativeForContract(
                            $dailyWork->contract_id,
                            $dailyWork->work_type,
                            $dailyWork->unit,
                            $dailyWork->work_date->format('Y-m-d')
                        );
                    @endphp
                    <tr>
                        <td>{{ $dailyWork->work_date?->format('Y-m-d') }}</td>
                        <td>{{ $dailyWork->contract?->contract_number }}</td>
                        <td>{{ $dailyWork->contract?->contract_name }}</td>
                        <td>{{ $dailyWork->work_type }}</td>
                        <td>{{ $dailyWork->unit ?: 'غير محدد' }}</td>
                        <td>{{ $dailyWork->daily_quantity ?? '—' }}</td>
                        <td>{{ number_format($total, 3) }}</td>
                        <td style="max-width: 180px;">{{ $dailyWork->details ?: '—' }}</td>
                        <td>
                            @foreach ($dailyWork->photos as $photo)
                                <a href="{{ asset('storage/'.$photo->photo_path) }}" target="_blank">
                                    <img src="{{ asset('storage/'.$photo->photo_path) }}" style="width:50px;height:50px;object-fit:cover;border-radius:8px;margin:2px;" alt="صورة العمل">
                                </a>
                            @endforeach
                        </td>
                        <td>
                            <div class="d-flex flex-column gap-2">
                                <a href="{{ route('daily-works.show', $dailyWork) }}" class="btn btn-sm btn-info text-white">عرض</a>
                                <a href="{{ route('daily-works.edit', $dailyWork) }}" class="btn btn-sm btn-warning">تعديل</a>
                                <form action="{{ route('daily-works.destroy', $dailyWork) }}" method="POST" onsubmit="return confirm('هل أنت متأكد من حذف هذا البند؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger w-100">حذف</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center text-muted">لا توجد أعمال مسجلة.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{ $dailyWorks->links() }}
    </div>
@endsection
