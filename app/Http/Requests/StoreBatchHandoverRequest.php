<?php

namespace App\Http\Requests;

use App\Models\ItemType;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreBatchHandoverRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', 'in:PENYERAHAN,PENGEMBALIAN,PEMINJAMAN,PENGGANTIAN,MUTASI'],
            'receiver_ids' => ['required', 'array', 'min:1'],
            'receiver_ids.*' => ['required', 'integer', 'distinct', 'exists:users,id', Rule::notIn([$this->user()?->id])],
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
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'receiver_ids.required' => 'Pilih minimal satu penerima.',
            'receiver_ids.min' => 'Pilih minimal satu penerima.',
            'receiver_ids.*.not_in' => 'Pihak menerima tidak boleh sama dengan pihak yang menyerahkan.',
            'items.required' => 'Tambahkan minimal satu item.',
            'items.min' => 'Tambahkan minimal satu item.',
        ];
    }

    /**
     * @return array<int, callable(Validator): void>
     */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->has('receiver_ids') || ! is_array($this->input('receiver_ids'))) {
                return;
            }

            $inactiveReceiverIds = collect($this->input('receiver_ids'))
                ->diff($this->user()?->newQuery()->where('active', true)->whereIn('id', $this->input('receiver_ids'))->pluck('id') ?? [])
                ->values();

            if ($inactiveReceiverIds->isNotEmpty()) {
                $validator->errors()->add('receiver_ids', 'Semua penerima harus merupakan user aktif.');
            }

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

            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $itemTypeIds = collect($this->input('items', []))->pluck('item_type_id')->filter();
            $apdItemTypeIds = ItemType::query()
                ->whereIn('id', $itemTypeIds)
                ->whereHas('category', fn ($query) => $query->where('name', 'APD'))
                ->pluck('id');

            if ($apdItemTypeIds->isNotEmpty() && $apdItemTypeIds->count() !== $itemTypeIds->count()) {
                $validator->errors()->add('items', 'Batch APD harus berisi seluruhnya item kategori APD dan tidak dapat dicampur dengan item lain.');
            }
        }];
    }
}
