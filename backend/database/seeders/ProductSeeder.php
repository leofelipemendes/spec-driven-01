<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ProductSeeder extends Seeder
{
    private const NAMES = [
        'Notebook Essencial',
        'Fone Bluetooth',
        'Teclado Mecanico',
        'Mouse Optico',
        'Monitor Full HD',
        'Cadeira de Escritorio',
        'Suporte Ajustavel',
        'Webcam HD',
        'Carregador USB',
        'Mochila Urbana',
    ];

    private const CATEGORIES = [
        'Informatica',
        'Acessorios',
        'Audio',
        'Escritorio',
        'Mobilidade',
    ];

    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('ProductSeeder is disabled in production.');
        }

        DB::transaction(function (): void {
            DB::table('product_activities')->delete();
            DB::table('products')->delete();

            $timestamp = now()->toDateTimeString();
            $products = [];

            for ($index = 1; $index <= 100; $index++) {
                $variation = ($index - 1) % 10;

                $products[] = [
                    'name' => self::NAMES[$variation],
                    'price' => number_format(10 + $variation + ($variation * 0.05), 2, '.', ''),
                    'category' => self::CATEGORIES[($index - 1) % count(self::CATEGORIES)],
                    'quantity' => $variation,
                    'seed_key' => sprintf('demo-product-%03d', $index),
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                    'deleted_at' => null,
                ];
            }

            DB::table('products')->insert($products);
        });
    }
}
