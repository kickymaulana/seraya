<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isAdmin() ?? false;
    }

    public function rules(): array
    {
        return [
            'role' => ['required', Rule::in(['admin', 'operator', 'viewer'])],
            'active' => ['required', 'boolean'],
            'position_id' => ['nullable', 'integer', Rule::exists('positions', 'id')],
            'department_ids' => ['required', 'array', 'min:1'],
            'department_ids.*' => ['integer', 'distinct', Rule::exists('departments', 'id')],
            'factory_ids' => ['required', 'array', 'min:1'],
            'factory_ids.*' => ['integer', 'distinct', Rule::exists('factories', 'id')],
        ];
    }
}
