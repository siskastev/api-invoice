<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

class ProductTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        // create role admin
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        // Auth Sanctum with role Admin
        Sanctum::actingAs($admin);
    }

    public function testCreateProductsValidationRequired()
    {
        $response = $this->postJson('/api/products', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'qty',
                'unit_price'
            ]);
    }

    public function testCreateProductsSuccess()
    {
        $attributeProduct = $this->mappingAttributeProduct();

        $payload = [
            "name" => 'Product 1',
            "type" => $attributeProduct['type'],
            "qty" => $attributeProduct['qty'],
            "unit_price" => $attributeProduct['unit_price'],
            "total_price" => $attributeProduct['total_price']
        ];

        $response = $this->postJson('/api/products', $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product created successfully',
                'data' => $payload,
            ]);
    }

    public function testProductsNotFound()
    {
        $response = $this->getJson('/api/products/001');

        $response->assertStatus(404)
            ->assertJson([
                'message' => '001 product not found!!'
            ]);
    }

    public function testUpdateProductsSuccess()
    {
        $attributeProduct = $this->mappingAttributeProduct();

        $product = Product::factory()->create([
            "name" => 'Product 1',
            "type" => $attributeProduct['type'],
            "qty" => $attributeProduct['qty'],
            "unit_price" => $attributeProduct['unit_price'],
            "total_price" => $attributeProduct['total_price']
        ]);

        $payload = [
            "name" => "ini service baru",
            "type" => "service",
            "qty" => $product->qty,
            "unit_price" => $product->unit_price,
            "total_price" => $product->total_price
        ];

        $response = $this->putJson('/api/products/' . $product->id, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Product update successfully',
                'data' => $payload
            ]);
    }

    public function testSoftDeleteProducts()
    {
        $attributeProduct = $this->mappingAttributeProduct();

        $product = Product::factory()->create([
            "name" => "Product 1",
            "type" => $attributeProduct['type'],
            "qty" => $attributeProduct['qty'],
            "unit_price" => $attributeProduct['unit_price'],
            "total_price" => $attributeProduct['total_price']
        ]);

        $response = $this->deleteJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJson([
                'message' => sprintf('Product %s delete successfully', $product->id)
            ]);

        $this->assertSoftDeleted($product);
    }


    public function mappingAttributeProduct(): array
    {
        $qty = $this->faker->numberBetween(1, 100);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);
        $totalPrice = $qty * $unitPrice;

        return [
            "type" => $this->faker->randomElement(['service', 'hardware', 'subscription']),
            "qty" => $qty,
            "unit_price" => $unitPrice,
            "total_price" => $totalPrice
        ];
    }
}
