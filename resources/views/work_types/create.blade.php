@extends('layouts.app')

@section('title', 'إضافة نوع عمل')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">إضافة نوع عمل</h2>
        <a href="{{ route('work-types.index') }}" class="btn btn-outline-secondary">العودة</a>
    </div>

    <div class="card p-4">
        <form action="{{ route('work-types.store') }}" method="POST" data-turbo="false">
            @csrf
            <input type="hidden" name="is_active" value="0">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">نوع العمل</label>
                    <input type="text" name="name" value="{{ old('name') }}" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">الوحدة</label>
                    <input type="text" name="unit" value="{{ old('unit') }}" class="form-control" required maxlength="80">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="work-type-active" {{ old('is_active', true) ? 'checked' : '' }}>
                        <label class="form-check-label" for="work-type-active">نشط</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">حفظ نوع العمل</button>
            </div>
        </form>
    </div>
@endsection
