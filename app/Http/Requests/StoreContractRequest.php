<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'contract_number' => ['required', 'string', 'max:50', 'unique:contracts,contract_number'],
            'contract_name' => ['required', 'string', 'max:255'],
            'supervisor_name' => ['required', 'string', 'max:255'],
            'executing_company' => ['required', 'string', 'max:255'],
            'assignment_date' => ['required', 'date'],
            'supervisory_members' => ['nullable', 'array'],
            'supervisory_members.*' => ['required', 'string', 'max:255'],
        ];
    }
}
