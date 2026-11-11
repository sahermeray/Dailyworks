@extends('layouts.app')

@section('title', 'إدارة أنواع العمل')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">إدارة أنواع العمل</h2>
        <a href="{{ route('work-types.create') }}" class="btn btn-primary">إضافة نوع عمل</a>
    </div>

    <div class="card p-4">
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead>
                <tr>
                    <th>نوع العمل</th>
                    <th>الوحدة</th>
                    <th>الحالة</th>
                    <th>الإجراءات</th>
                </tr>
                </thead>
                <tbody>
                @forelse ($workTypes as $workType)
                    <tr>
                        <td>{{ $workType->name }}</td>
                        <td>{{ $workType->unit }}</td>
                        <td>
                            <span class="badge {{ $workType->is_active ? 'bg-success' : 'bg-secondary' }}">
                                {{ $workType->is_active ? 'نشط' : 'غير نشط' }}
                            </span>
                        </td>
                        <td>
                            <a href="{{ route('work-types.edit', $workType) }}" class="btn btn-sm btn-warning">تعديل</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">لا توجد أنواع عمل مسجلة.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
