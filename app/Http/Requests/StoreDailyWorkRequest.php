<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreDailyWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract_id' => ['required', 'exists:contracts,id'],
            'work_date' => ['required', 'date'],
            'work_type' => ['required', 'string', 'in:قشط الزفت المهترئ,تقديم ورش مانع اسفلتي MC0,ترحيل أتربة,تقديم وتنفيذ طبقة مجبول أسفلتي,تقديم ومد ودحي مجبول أسفلتي,قص وترقيع بالمجبول الأسفلتي,مد قميص أسفلتي'],
            'unit' => ['nullable', 'string', 'in:متر,متر مربع,متر مكعب,'],
            'daily_quantity' => ['nullable', 'numeric', 'min:0'],
            'details' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
