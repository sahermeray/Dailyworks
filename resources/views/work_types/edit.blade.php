@extends('layouts.app')

@section('title', 'تعديل نوع العمل')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">تعديل نوع العمل</h2>
        <a href="{{ route('work-types.index') }}" class="btn btn-outline-secondary">العودة</a>
    </div>

    <div class="card p-4">
        <form action="{{ route('work-types.update', $workType) }}" method="POST" data-turbo="false">
            @csrf
            @method('PUT')
            <input type="hidden" name="is_active" value="0">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">نوع العمل</label>
                    <input type="text" name="name" value="{{ old('name', $workType->name) }}" class="form-control" required maxlength="255">
                </div>
                <div class="col-md-4">
                    <label class="form-label">الوحدة</label>
                    <input type="text" name="unit" value="{{ old('unit', $workType->unit) }}" class="form-control" required maxlength="80">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input type="checkbox" name="is_active" value="1" class="form-check-input" id="work-type-active" {{ old('is_active', $workType->is_active) ? 'checked' : '' }}>
                        <label class="form-check-label" for="work-type-active">نشط</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            </div>
        </form>
    </div>
@endsection
