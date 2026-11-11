<?php

namespace App\Http\Requests;

use App\Models\DailyWork;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDailyWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dailyWork = $this->route('daily_work');

        return [
            'contract_id' => ['required', 'exists:contracts,id'],
            'work_date' => ['required', 'date'],
            'work_type_id' => [
                'required',
                Rule::exists('work_types', 'id')->where(function ($query) use ($dailyWork): void {
                    $query->where('is_active', true);

                    if ($dailyWork instanceof DailyWork && $dailyWork->work_type_id !== null) {
                        $query->orWhere('id', $dailyWork->work_type_id);
                    }
                }),
            ],
            'daily_quantity' => ['nullable', 'numeric', 'min:0'],
            'details' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
