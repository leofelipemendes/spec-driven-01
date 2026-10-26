<?php

namespace Tests\Feature\Api;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductListingTest extends TestCase
{
    use RefreshDatabase;

    public function test_listing_is_paginated_and_preserves_filters_in_navigation_links(): void
    {
        for ($index = 1; $index <= 16; $index++) {
            $this->createProduct("Widget {$index}", 'Tools', '19.90', $index);
        }

        $this->getJson('/api/products')->assertOk()
            ->assertJsonPath('meta.per_page', 15);
        $this->getJson('/api/products?per_page=100')->assertOk()
            ->assertJsonPath('meta.per_page', 100);

        $firstDefaultPage = $this->getJson('/api/products?page=1')
            ->assertOk()
            ->json('data.*.id');
        $secondDefaultPage = $this->getJson('/api/products?page=2')
            ->assertOk()
            ->json('data.*.id');
        $this->assertCount(15, $firstDefaultPage);
        $this->assertCount(1, $secondDefaultPage);
        $this->assertCount(16, array_unique([...$firstDefaultPage, ...$secondDefaultPage]));

        $response = $this->getJson('/api/products?name=Widget&per_page=1')
            ->assertOk()
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 16);

        $this->assertCount(1, $response->json('data'));
        $this->assertStringContainsString('name=Widget', $response->json('links.next'));
        $this->assertStringContainsString('per_page=1', $response->json('links.next'));
    }

    public function test_independent_and_combined_filters_apply_to_active_products(): void
    {
        $matching = $this->createProduct('Red Widget', 'Tools', '10.00', 2);
        $this->createProduct('Red Wrench', 'Tools', '20.00', 2);
        $this->createProduct('Blue Widget', 'Tools', '10.00', 2);
        $this->createProduct('Red Widget', 'Garden', '10.00', 2);
        $deleted = $this->createProduct('Red Widget deleted', 'Tools', '10.00', 2);
        $deleted->delete();

        $this->getJson('/api/products?name=red')->assertOk()
            ->assertJsonCount(3, 'data');
        $this->getJson('/api/products?category=Tools')->assertOk()
            ->assertJsonCount(3, 'data');
        $this->getJson('/api/products?price=10.00')->assertOk()
            ->assertJsonCount(3, 'data');
        $this->getJson('/api/products?quantity=2')->assertOk()
            ->assertJsonCount(4, 'data');
        $this->getJson('/api/products?name=red&price=10.00&category=Tools&quantity=2')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $matching->id);
    }

    public function test_empty_results_and_invalid_filter_parameters_are_handled(): void
    {
        $this->getJson('/api/products?name=does-not-exist')->assertOk()
            ->assertJsonCount(0, 'data')
            ->assertJsonPath('meta.total', 0);

        $this->getJson('/api/products?page=0')->assertUnprocessable()
            ->assertJsonValidationErrors('page');
        $this->getJson('/api/products?per_page=101')->assertUnprocessable()
            ->assertJsonValidationErrors('per_page');
        $this->getJson('/api/products?price=10.123')->assertUnprocessable()
            ->assertJsonValidationErrors('price');
    }

    private function createProduct(string $name, string $category, string $price, int $quantity): Product
    {
        return Product::query()->create(compact('name', 'category', 'price', 'quantity'));
    }
}
