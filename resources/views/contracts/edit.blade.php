@extends('layouts.app')

@section('title', 'تعديل العقد')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">تعديل العقد</h2>
        <a href="{{ route('contracts.show', $contract) }}" class="btn btn-outline-secondary">عرض التفاصيل</a>
    </div>

    <div class="card p-4">
        <form action="{{ route('contracts.update', $contract) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">رقم العقد</label>
                    <input type="text" name="contract_number" value="{{ old('contract_number', $contract->contract_number) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">اسم العقد</label>
                    <input type="text" name="contract_name" value="{{ old('contract_name', $contract->contract_name) }}" class="form-control" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">اسم المشرف</label>
                    <input type="text" name="supervisor_name" value="{{ old('supervisor_name', $contract->supervisor_name) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">الشركة المنفذة</label>
                    <input type="text" name="executing_company" value="{{ old('executing_company', $contract->executing_company) }}" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">تاريخ الإسناد</label>
                    <input type="date" name="assignment_date" value="{{ old('assignment_date', $contract->assignment_date?->format('Y-m-d')) }}" class="form-control" required>
                </div>
                <div class="col-12">
                    <label class="form-label">أعضاء الإشراف</label>
                    <div id="members-wrapper">
                        @forelse ($contract->supervisoryMembers as $member)
                            <input type="text" name="supervisory_members[]" value="{{ $member->member_name }}" class="form-control mb-2">
                        @empty
                            <input type="text" name="supervisory_members[]" class="form-control mb-2" placeholder="اسم عضو الإشراف">
                        @endforelse
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="add-member-btn">إضافة عضو آخر</button>
                </div>
            </div>

            <div class="mt-4 text-end">
                <button type="submit" class="btn btn-primary">حفظ التعديلات</button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const wrapper = document.getElementById('members-wrapper');
            const button = document.getElementById('add-member-btn');

            button.addEventListener('click', function () {
                const input = document.createElement('input');
                input.type = 'text';
                input.name = 'supervisory_members[]';
                input.className = 'form-control mb-2';
                input.placeholder = 'اسم عضو الإشراف';
                wrapper.appendChild(input);
            });
        });
    </script>
@endsection
