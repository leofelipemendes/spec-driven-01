<?php

namespace Tests\Unit\Http\Requests;

use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class ProductRequestTest extends TestCase
{
    public function test_product_text_price_and_quantity_validation_rules(): void
    {
        $validProduct = [
            'name' => str_repeat('a', 255),
            'price' => '0',
            'category' => str_repeat('b', 255),
            'quantity' => 0,
        ];

        $this->assertTrue(Validator::make($validProduct, (new StoreProductRequest)->rules())->passes());

        $invalidCases = [
            ['name' => '   ', 'price' => '1.00', 'category' => 'Valid', 'quantity' => 0],
            ['name' => str_repeat('a', 256), 'price' => '1.00', 'category' => 'Valid', 'quantity' => 0],
            ['name' => 'Valid', 'price' => '-0.01', 'category' => 'Valid', 'quantity' => 0],
            ['name' => 'Valid', 'price' => '1.001', 'category' => 'Valid', 'quantity' => 0],
            ['name' => 'Valid', 'price' => '1.00', 'category' => 'Valid', 'quantity' => -1],
            ['name' => 'Valid', 'price' => '1.00', 'category' => 'Valid', 'quantity' => 1.5],
        ];

        foreach ($invalidCases as $input) {
            $this->assertTrue(
                Validator::make($input, (new UpdateProductRequest)->rules())->fails(),
                'Expected validation to fail for: '.json_encode($input),
            );
        }
    }
}
