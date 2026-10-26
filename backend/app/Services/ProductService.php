<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function create(array $attributes): Product
    {
        return DB::transaction(function () use ($attributes): Product {
            $product = Product::query()->create($attributes);
            $this->recordActivity($product, 'created');

            return $product;
        });
    }

    public function find(int $id): Product
    {
        return Product::query()->findOrFail($id);
    }

    public function update(int $id, array $attributes): Product
    {
        return DB::transaction(function () use ($id, $attributes): Product {
            $product = Product::query()->findOrFail($id);
            $product->fill($attributes);
            $product->save();
            $this->recordActivity($product, 'updated');

            return $product;
        });
    }

    public function delete(int $id): void
    {
        DB::transaction(function () use ($id): void {
            $product = Product::query()->findOrFail($id);
            $product->delete();
            $this->recordActivity($product, 'deleted');
        });
    }

    public function paginate(array $filters): LengthAwarePaginator
    {
        $query = Product::query();

        foreach (['name', 'category'] as $field) {
            $value = $filters[$field] ?? null;

            if ($value !== null && $value !== '') {
                $query->where($field, 'like', '%'.$value.'%');
            }
        }

        foreach (['price', 'quantity'] as $field) {
            $value = $filters[$field] ?? null;

            if ($value !== null && $value !== '') {
                $query->where($field, '=', $value);
            }
        }

        return $query->orderBy('id')
            ->paginate((int) ($filters['per_page'] ?? 15))
            ->withQueryString();
    }

    private function recordActivity(Product $product, string $type): void
    {
        $product->activities()->create([
            'activity_type' => $type,
            'created_at' => now(),
        ]);
    }
}
