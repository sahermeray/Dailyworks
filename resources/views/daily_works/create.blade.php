@extends('layouts.app')

@section('title', 'إضافة بند عمل')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">إضافة بند عمل يومي</h2>
        <a href="{{ route('daily-works.index') }}" class="btn btn-outline-secondary">العودة</a>
    </div>

    <div class="card p-4">
        <form action="{{ route('daily-works.store') }}" method="POST" enctype="multipart/form-data" data-turbo="false">
            @csrf
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">تاريخ العمل</label>
                    <input type="date" name="work_date" value="{{ old('work_date', now()->toDateString()) }}" class="form-control" required>
                </div>
                <div class="col-md-9">
                    <label class="form-label">العقد</label>
                    <select name="contract_id" class="form-select" required>
                        <option value="">اختر العقد</option>
                        @foreach ($contracts as $contract)
                            <option value="{{ $contract->id }}" {{ (string) old('contract_id', $selectedContractId ?? '') === (string) $contract->id ? 'selected' : '' }}>{{ $contract->contract_number }} - {{ $contract->contract_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">نوع العمل</label>
                    <select name="work_type" id="work-type-create" class="form-select" data-auto-select-unit="{{ old('unit') === null ? 'true' : 'false' }}" required>
                        <option value="">اختر نوع العمل</option>
                        @foreach ($workTypeUnits as $type => $unit)
                            <option value="{{ $type }}" data-unit="{{ $unit }}" {{ old('work_type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">الوحدة</label>
                    <select name="unit" id="unit-create" class="form-select">
                        <option value="">فارغة</option>
                        @foreach ($units as $unit)
                            <option value="{{ $unit }}" {{ old('unit') == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">الكمية اليومية</label>
                    <input type="number" step="any" min="0" inputmode="decimal" pattern="[0-9]*[.,]?[0-9]*" name="daily_quantity" value="{{ old('daily_quantity') }}" class="form-control quantity-input">
                </div>
                <div class="col-md-3">
                    <label class="form-label">صورة</label>
                    <input type="file" name="photos[]" multiple accept="image/jpeg,image/png,image/webp" class="form-control" id="photo-input-create">
                    <div id="photo-preview-create" class="d-flex flex-wrap gap-2 mt-2"></div>
                </div>
                <div class="col-12">
                    <label class="form-label">التفاصيل</label>
                    <textarea name="details" class="form-control" rows="4">{{ old('details') }}</textarea>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">حفظ البند</button>
            </div>
        </form>
    </div>

@endsection
