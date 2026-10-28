<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

abstract class UppercaseFormRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(collect($this->all())
            ->filter(fn (mixed $value): bool => is_string($value))
            ->map(fn (string $value): string => mb_strtoupper($value))
            ->all());
    }
}
