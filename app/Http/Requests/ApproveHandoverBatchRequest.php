<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ApproveHandoverBatchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole('hse') ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'rejected_items' => ['nullable', 'array'],
            'rejected_items.*.id' => ['required', 'integer', 'distinct'],
            'rejected_items.*.reason' => ['required', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $batch = $this->route('handoverBatch');

            if ($batch === null) {
                return;
            }

            $itemIds = collect($this->input('rejected_items', []))->pluck('id');
            $validItemIds = $batch->handovers()->with('items')->get()->pluck('items')->flatten()->pluck('id');

            if ($itemIds->diff($validItemIds)->isNotEmpty()) {
                $validator->errors()->add('rejected_items', 'Item yang ditolak harus berasal dari batch ini.');
            }
        }];
    }
}
