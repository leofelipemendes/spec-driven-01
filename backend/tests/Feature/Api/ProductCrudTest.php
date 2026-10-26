<?php

namespace Tests\Feature\Api;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_crud_records_mutations_and_keeps_reads_out_of_history(): void
    {
        $product = $this->postJson('/api/products', [
            'name' => '  Produto teste  ',
            'price' => '19.9',
            'category' => 'Acessorios',
            'quantity' => 5,
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Produto teste')
            ->assertJsonPath('data.price', '19.90')
            ->json('data');

        $this->assertDatabaseCount('product_activities', 1);
        $this->assertDatabaseHas('product_activities', [
            'product_id' => $product['id'],
            'activity_type' => 'created',
        ]);

        $this->getJson("/api/products/{$product['id']}")
            ->assertOk()
            ->assertJsonPath('data.name', 'Produto teste');
        $this->getJson('/api/products')->assertOk();
        $this->assertDatabaseCount('product_activities', 1);

        $this->putJson("/api/products/{$product['id']}", [
            'name' => 'Produto atualizado',
            'price' => '25.00',
            'category' => 'Acessorios',
            'quantity' => 8,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Produto atualizado');

        $this->assertDatabaseHas('product_activities', [
            'product_id' => $product['id'],
            'activity_type' => 'updated',
        ]);
        $this->assertDatabaseCount('product_activities', 2);

        $this->putJson("/api/products/{$product['id']}", [
            'name' => 'Produto atualizado novamente',
            'price' => '26.00',
            'category' => 'Acessorios',
            'quantity' => 9,
        ])->assertOk()
            ->assertJsonPath('data.name', 'Produto atualizado novamente');
        $this->assertDatabaseCount('product_activities', 3);

        $this->deleteJson("/api/products/{$product['id']}")->assertNoContent();
        $this->assertSoftDeleted('products', ['id' => $product['id']]);
        $this->assertDatabaseCount('product_activities', 4);
        $this->assertDatabaseHas('product_activities', [
            'product_id' => $product['id'],
            'activity_type' => 'deleted',
        ]);
        $this->getJson("/api/products/{$product['id']}")
            ->assertNotFound()
            ->assertJsonPath('message', 'Recurso não encontrado.');
        $this->assertDatabaseCount('product_activities', 4);
    }

    public function test_invalid_input_does_not_create_or_change_products(): void
    {
        $this->postJson('/api/products', [
            'name' => '   ',
            'price' => '-1',
            'category' => '   ',
            'quantity' => -2,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'price', 'category', 'quantity'])
            ->assertJsonPath('errors.name.0', 'O campo nome é obrigatório.');

        $product = $this->postJson('/api/products', [
            'name' => 'Produto válido',
            'price' => '10.00',
            'category' => 'Categoria',
            'quantity' => 1,
        ])->assertCreated()->json('data');

        $this->putJson("/api/products/{$product['id']}", [
            'name' => 'Produto alterado',
            'price' => '10.123',
            'category' => 'Categoria',
            'quantity' => 1,
        ])->assertUnprocessable()->assertJsonValidationErrors('price');

        $this->assertDatabaseHas('products', [
            'id' => $product['id'],
            'name' => 'Produto válido',
            'price' => '10.00',
        ]);
        $this->assertDatabaseCount('product_activities', 1);
    }

    public function test_product_endpoint_does_not_expose_internal_activity_history(): void
    {
        $this->getJson('/api/products/1/activities')->assertNotFound();
    }

    public function test_patch_updates_only_provided_fields_and_rejects_empty_updates(): void
    {
        $product = $this->postJson('/api/products', [
            'name' => 'Produto original',
            'price' => '10.00',
            'category' => 'Categoria original',
            'quantity' => 4,
        ])->assertCreated()->json('data');

        $this->patchJson("/api/products/{$product['id']}", [
            'name' => 'Nome alterado',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Nome alterado')
            ->assertJsonPath('data.price', '10.00')
            ->assertJsonPath('data.category', 'Categoria original')
            ->assertJsonPath('data.quantity', 4);

        $this->assertDatabaseCount('product_activities', 2);

        $this->patchJson("/api/products/{$product['id']}", [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('product');

        $this->assertDatabaseCount('product_activities', 2);
    }
}
