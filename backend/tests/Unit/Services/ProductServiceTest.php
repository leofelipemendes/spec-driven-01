<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductActivity;
use App\Services\ProductService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ProductServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_mutations_create_activity_and_read_does_not(): void
    {
        $service = app(ProductService::class);

        $product = $service->create($this->validProduct());
        $this->assertInstanceOf(Product::class, $product);
        $this->assertSame('created', $product->activities()->sole()->activity_type);

        $this->assertSame($product->id, $service->find($product->id)->id);
        $this->assertDatabaseCount('product_activities', 1);

        $updated = $service->update($product->id, [
            ...$this->validProduct(),
            'name' => 'Updated product',
        ]);
        $this->assertSame('Updated product', $updated->name);
        $this->assertSame('updated', $updated->activities()->latest('id')->first()->activity_type);

        $service->delete($product->id);
        $this->assertSoftDeleted('products', ['id' => $product->id]);
        $this->assertDatabaseCount('product_activities', 3);
    }

    public function test_service_reports_missing_products(): void
    {
        $this->expectException(ModelNotFoundException::class);

        app(ProductService::class)->find(999);
    }

    public function test_activity_failure_rolls_back_product_creation(): void
    {
        ProductActivity::creating(function (): void {
            throw new RuntimeException('Activity persistence failed.');
        });

        try {
            app(ProductService::class)->create($this->validProduct());
            $this->fail('The failed activity should abort product creation.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Activity persistence failed.', $exception->getMessage());
        } finally {
            ProductActivity::flushEventListeners();
        }

        $this->assertDatabaseCount('products', 0);
        $this->assertDatabaseCount('product_activities', 0);
    }

    private function validProduct(): array
    {
        return [
            'name' => 'Test product',
            'price' => '19.90',
            'category' => 'Accessories',
            'quantity' => 5,
        ];
    }
}
