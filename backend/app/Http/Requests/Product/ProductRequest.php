<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

abstract class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $trimmed = [];

        foreach (['name', 'category'] as $field) {
            $value = $this->input($field);

            if (is_string($value)) {
                $trimmed[$field] = trim($value);
            }
        }

        $this->merge($trimmed);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/\\S/u'],
            'price' => ['required', 'string', 'regex:/^(0|[1-9][0-9]*)(\\.[0-9]{1,2})?$/D'],
            'category' => ['required', 'string', 'max:255', 'regex:/\\S/u'],
            'quantity' => ['required', 'integer', 'min:0'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $unexpected = array_diff(array_keys($this->all()), array_keys($this->rules()));

            foreach ($unexpected as $field) {
                $validator->errors()->add($field, __('validation.unexpected_field', ['attribute' => $field]));
            }
        });
    }
}
