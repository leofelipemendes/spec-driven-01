<?php

namespace App\Http\Requests\Product;

use Illuminate\Validation\Validator;

class UpdateProductRequest extends ProductRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        if ($this->isMethod('PATCH')) {
            foreach ($rules as &$fieldRules) {
                $fieldRules[0] = 'sometimes';
            }
        }

        return $rules;
    }

    public function withValidator(Validator $validator): void
    {
        parent::withValidator($validator);

        if ($this->isMethod('PATCH')) {
            $validator->after(function (Validator $validator): void {
                $hasProductField = array_intersect(
                    ['name', 'price', 'category', 'quantity'],
                    array_keys($this->all()),
                ) !== [];

                if (! $hasProductField) {
                    $validator->errors()->add('product', 'Informe ao menos um campo do produto.');
                }
            });
        }
    }
}
