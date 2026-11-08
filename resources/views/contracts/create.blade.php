@extends('layouts.app')

@section('title', 'إضافة عقد جديد')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">إضافة عقد جديد</h2>
        <a href="{{ route('contracts.index') }}" class="btn btn-outline-secondary">العودة</a>
    </div>

    <div class="card p-4">
        <form action="{{ route('contracts.store') }}" method="POST" data-turbo="false">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">رقم العقد</label>
                    <input type="text" name="contract_number" value="{{ old('contract_number') }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">اسم العقد</label>
                    <input type="text" name="contract_name" value="{{ old('contract_name') }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">اسم المشرف</label>
                    <input type="text" name="supervisor_name" value="{{ old('supervisor_name') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الشركة المنفذة</label>
                    <input type="text" name="executing_company" value="{{ old('executing_company') }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ الإسناد</label>
                    <input type="date" name="assignment_date" value="{{ old('assignment_date') }}" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">أعضاء الإشراف</label>
                    <div id="members-wrapper">
                        <input type="text" name="supervisory_members[]" class="form-control mb-2" placeholder="اسم عضو الإشراف">
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-member-btn">إضافة عضو آخر</button>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">حفظ العقد</button>
            </div>
        </form>
    </div>

@endsection
