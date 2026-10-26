<?php

namespace Tests\Feature\Database;

use App\Models\Product;
use Database\Seeders\ProductSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_seed_run_replaces_all_products_and_activity_history(): void
    {
        $oldProduct = Product::query()->create([
            'name' => 'API product',
            'price' => '15.00',
            'category' => 'Old category',
            'quantity' => 7,
        ]);
        $oldProduct->activities()->create([
            'activity_type' => 'created',
            'created_at' => now(),
        ]);

        $seeder = new ProductSeeder;
        $seeder->run();

        $this->assertDemoCatalog();

        $newProduct = Product::query()->create([
            'name' => 'Another API product',
            'price' => '20.00',
            'category' => 'Other category',
            'quantity' => 3,
        ]);
        $newProduct->activities()->create([
            'activity_type' => 'created',
            'created_at' => now(),
        ]);

        $seeder->run();

        $this->assertDemoCatalog();
        $this->assertDatabaseMissing('products', ['id' => $oldProduct->id]);
        $this->assertDatabaseMissing('products', ['id' => $newProduct->id]);
    }

    public function test_failed_seed_run_rolls_back_catalog_and_history_cleanup(): void
    {
        $product = Product::query()->create([
            'name' => 'Existing product',
            'price' => '15.00',
            'category' => 'Existing category',
            'quantity' => 7,
        ]);
        $product->activities()->create([
            'activity_type' => 'created',
            'created_at' => now(),
        ]);
        DB::unprepared(
            'CREATE TRIGGER reject_second_demo_product BEFORE INSERT ON products '.
            "WHEN NEW.seed_key = 'demo-product-002' BEGIN SELECT RAISE(ABORT, 'seed failure'); END",
        );

        try {
            (new ProductSeeder)->run();
            $this->fail('The triggered insert failure should abort the seed transaction.');
        } catch (QueryException) {
        } finally {
            DB::unprepared('DROP TRIGGER IF EXISTS reject_second_demo_product');
        }

        $this->assertDatabaseHas('products', ['id' => $product->id, 'name' => 'Existing product']);
        $this->assertDatabaseHas('product_activities', [
            'product_id' => $product->id,
            'activity_type' => 'created',
        ]);
        $this->assertDatabaseCount('products', 1);
        $this->assertDatabaseCount('product_activities', 1);
    }

    private function assertDemoCatalog(): void
    {
        $this->assertDatabaseCount('products', 100);
        $this->assertDatabaseCount('product_activities', 0);
        $this->assertSame(100, Product::query()->whereNull('deleted_at')->count());
        $this->assertSame(100, DB::table('products')->whereNotNull('seed_key')->count());
        $this->assertSame(10, DB::table('products')->distinct()->count('name'));
        $this->assertSame(5, DB::table('products')->distinct()->count('category'));
        $this->assertSame(10, DB::table('products')->distinct()->count('price'));
        $this->assertSame(10, DB::table('products')->distinct()->count('quantity'));
    }
}
