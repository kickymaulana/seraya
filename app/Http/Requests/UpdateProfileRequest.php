<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'position_id' => ['required', 'integer', Rule::exists('positions', 'id')->where('is_active', true)],
            'department_ids' => ['required', 'array', 'min:1'],
            'department_ids.*' => ['integer', 'distinct', Rule::exists('departments', 'id')->where('is_active', true)],
            'factory_ids' => ['required', 'array', 'min:1'],
            'factory_ids.*' => ['integer', 'distinct', Rule::exists('factories', 'id')->where('is_active', true)],
        ];
    }
}
