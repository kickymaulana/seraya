<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreHandoverRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:PENYERAHAN,PENGEMBALIAN,PEMINJAMAN,PENGGANTIAN,MUTASI'],
            'receiver_id' => ['required', 'integer', 'exists:users,id', Rule::notIn([$this->user()?->id])],
            'transaction_date' => ['required', 'date'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_type_id' => ['nullable', 'integer', 'exists:item_types,id'],
            'items.*.manual_name' => ['nullable', 'string', 'max:255'],
            'items.*.manual_unit' => ['nullable', 'string', 'max:50'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.condition' => ['required', 'string', 'max:50'],
            'items.*.notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get the custom validation messages for the request.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'receiver_id.not_in' => 'Pihak menerima tidak boleh sama dengan pihak yang menyerahkan.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            foreach ($this->input('items', []) as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                if (blank($item['item_type_id'] ?? null) && blank($item['manual_name'] ?? null)) {
                    $validator->errors()->add("items.{$index}.manual_name", 'Pilih item atau isi nama barang manual.');
                }

                if (filled($item['item_type_id'] ?? null) && filled($item['manual_name'] ?? null)) {
                    $validator->errors()->add("items.{$index}.manual_name", 'Gunakan pilihan master atau nama manual, bukan keduanya.');
                }
            }
        }];
    }
}
