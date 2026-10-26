<?php

namespace Tests\Unit\Seeders;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_refuses_production_before_writing(): void
    {
        $product = Product::query()->create([
            'name' => 'Keep this product',
            'price' => '5.00',
            'category' => 'Existing',
            'quantity' => 1,
        ]);
        $this->app['env'] = 'production';

        try {
            (new ProductSeeder)->run();
            $this->fail('The seeder must reject production before writing.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('production', $exception->getMessage());
        } finally {
            $this->app['env'] = 'testing';
        }

        $this->assertDatabaseHas('products', ['id' => $product->id]);
        $this->assertDatabaseCount('products', 1);
    }

    public function test_seeder_generates_reproducible_demo_keys_and_varied_values(): void
    {
        $seeder = new ProductSeeder;
        $seeder->run();
        $firstRun = DB::table('products')
            ->orderBy('seed_key')
            ->get(['seed_key', 'name', 'category', 'price', 'quantity'])
            ->map(fn (object $product): array => (array) $product)
            ->all();

        $seeder->run();
        $secondRun = DB::table('products')
            ->orderBy('seed_key')
            ->get(['seed_key', 'name', 'category', 'price', 'quantity'])
            ->map(fn (object $product): array => (array) $product)
            ->all();

        $this->assertSame($firstRun, $secondRun);
        $this->assertSame('demo-product-001', $firstRun[0]['seed_key']);
        $this->assertSame('demo-product-100', $firstRun[99]['seed_key']);
        $this->assertSame(10, DB::table('products')->distinct()->count('name'));
        $this->assertSame(5, DB::table('products')->distinct()->count('category'));
        $this->assertSame(10, DB::table('products')->distinct()->count('price'));
        $this->assertSame(10, DB::table('products')->distinct()->count('quantity'));
    }
}
