<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'work_type_id' => ['required', Rule::exists('work_types', 'id')->where('is_active', true)],
            'daily_quantity' => ['nullable', 'numeric', 'min:0'],
            'details' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
