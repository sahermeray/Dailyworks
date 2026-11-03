<?php

namespace App\Http\Requests;

use App\Models\DailyWork;
use Illuminate\Foundation\Http\FormRequest;

class UpdateDailyWorkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workTypes = array_keys(DailyWork::WORK_TYPE_UNITS);
        $dailyWork = $this->route('daily_work');

        if ($dailyWork instanceof DailyWork && ! in_array($dailyWork->work_type, $workTypes, true)) {
            $workTypes[] = $dailyWork->work_type;
        }

        return [
            'contract_id' => ['required', 'exists:contracts,id'],
            'work_date' => ['required', 'date'],
            'work_type' => ['required', 'string', 'in:'.implode(',', $workTypes)],
            'unit' => ['nullable', 'string', 'in:'.implode(',', DailyWork::UNITS)],
            'daily_quantity' => ['nullable', 'numeric', 'min:0'],
            'details' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array'],
            'photos.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
        ];
    }
}
