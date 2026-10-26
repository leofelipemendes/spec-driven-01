<?php

return [
    'required' => 'O campo :attribute é obrigatório.',
    'string' => 'O campo :attribute deve ser um texto.',
    'integer' => 'O campo :attribute deve ser um número inteiro.',
    'min' => [
        'numeric' => 'O campo :attribute deve ser pelo menos :min.',
        'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
    ],
    'max' => [
        'numeric' => 'O campo :attribute não pode ser maior que :max.',
        'string' => 'O campo :attribute não pode ter mais que :max caracteres.',
    ],
    'between' => [
        'numeric' => 'O campo :attribute deve estar entre :min e :max.',
    ],
    'regex' => 'O formato do campo :attribute é inválido.',
    'unexpected_field' => 'O campo :attribute não é permitido.',
    'attributes' => [
        'name' => 'nome',
        'price' => 'preço',
        'category' => 'categoria',
        'quantity' => 'quantidade',
        'page' => 'página',
        'per_page' => 'itens por página',
    ],
];
