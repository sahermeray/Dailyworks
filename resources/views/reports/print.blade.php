<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>طباعة تقرير DailyWorks</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Tahoma', sans-serif;
            padding: 30px;
            direction: rtl;
        }
        .report-header {
            border-bottom: 2px solid #0d6efd;
            margin-bottom: 20px;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            font-size: 12px;
        }
        @media print {
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>
<div class="report-header">
    <h3 class="mb-1">تقرير الأعمال اليومية</h3>
    <div class="text-muted">تاريخ الطباعة: {{ now()->format('Y-m-d') }}</div>
</div>

<div class="mb-3">
    @if (!empty($filters['contract_id']))
        @php $contract = App\Models\Contract::find($filters['contract_id']); @endphp
        <div><strong>العقد:</strong> {{ $contract?->contract_number }} - {{ $contract?->contract_name }}</div>
    @else
        <div><strong>العقد:</strong> جميع العقود</div>
    @endif

    @if (!empty($filters['start_date']) || !empty($filters['end_date']))
        <div><strong>الفترة:</strong> {{ $filters['start_date'] ?? '—' }} إلى {{ $filters['end_date'] ?? '—' }}</div>
    @endif
</div>

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
    </tr>
    </thead>
    <tbody>
    @foreach ($dailyWorks as $dailyWork)
        @php
            $cumulative = app(App\Services\DailyWorkTotalService::class)->cumulativeForContract(
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
            <td>{{ number_format($cumulative, 3) }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="mt-3 no-print">
    <button type="button" class="btn btn-primary" onclick="window.print()">طباعة</button>
</div>
</body>
</html>
