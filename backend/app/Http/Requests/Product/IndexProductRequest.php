<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;

class IndexProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'nullable', 'string', 'max:255'],
            'price' => ['sometimes', 'nullable', 'string', 'regex:/^(0|[1-9][0-9]*)(\\.[0-9]{1,2})?$/D'],
            'category' => ['sometimes', 'nullable', 'string', 'max:255'],
            'quantity' => ['sometimes', 'nullable', 'integer', 'min:0'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
