<?php

namespace App\Http\Requests;

use App\Models\WorkType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateWorkTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $workType = $this->route('work_type');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('work_types', 'name')->ignore($workType instanceof WorkType ? $workType->id : null),
            ],
            'unit' => ['required', 'string', 'max:80'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
