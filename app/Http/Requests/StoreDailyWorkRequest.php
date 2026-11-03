<?php

namespace App\Http\Requests;

use App\Models\DailyWork;
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
            'work_type' => ['required', 'string', 'in:'.implode(',', array_keys(DailyWork::WORK_TYPE_UNITS))],
            'unit' => ['nullable', 'string', 'in:'.implode(',', DailyWork::UNITS)],
            'daily_quantity' => ['nullable', 'numeric', 'min:0'],
            'details' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
