<?php

namespace Tests\Unit\Services;

use App\Http\Requests\Product\IndexProductRequest;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_filter_validation_and_decimal_price_equality(): void
    {
        $rules = (new IndexProductRequest)->rules();
        $this->assertTrue(Validator::make(['page' => 1, 'per_page' => 100], $rules)->passes());
        $this->assertTrue(Validator::make(['price' => '19.90', 'quantity' => '0'], $rules)->passes());
        $this->assertTrue(Validator::make(['per_page' => 101], $rules)->fails());
        $this->assertTrue(Validator::make(['price' => '19.901'], $rules)->fails());

        $expected = $this->createProduct('Exact price', 'Tools', '19.90', 1);
        $this->createProduct('Other price', 'Tools', '19.99', 1);

        $results = app(ProductService::class)->paginate(['price' => '19.9']);

        $this->assertSame([$expected->id], $results->getCollection()->pluck('id')->all());
    }

    public function test_text_matching_ignores_ascii_case_but_not_accents(): void
    {
        $asciiMatch = $this->createProduct('Cafe Latte', 'Coffee', '2.00', 1);
        $this->createProduct('Café Latte', 'Coffee', '2.00', 1);
        $this->createProduct('Tea', 'Café', '2.00', 1);

        $nameResults = app(ProductService::class)->paginate(['name' => 'cAfE']);
        $categoryResults = app(ProductService::class)->paginate(['category' => 'cafe']);

        $this->assertSame([$asciiMatch->id], $nameResults->getCollection()->pluck('id')->all());
        $this->assertSame([], $categoryResults->getCollection()->pluck('id')->all());
    }

    private function createProduct(string $name, string $category, string $price, int $quantity): Product
    {
        return Product::query()->create(compact('name', 'category', 'price', 'quantity'));
    }
}
