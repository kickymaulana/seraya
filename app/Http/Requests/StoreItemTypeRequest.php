<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class StoreItemTypeRequest extends UppercaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100', 'unique:item_types,name'],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'unit' => ['required', 'string', 'max:30'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
