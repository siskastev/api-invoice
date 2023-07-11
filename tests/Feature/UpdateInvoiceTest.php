<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class UpdateInvoiceTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();

        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Sanctum::actingAs($admin);
    }

    public function testUpdateMissingInvoiceCode()
    {
        $this->json('put', 'api/invoice/01', $this->mappingPayloadInvoice())
            ->assertStatus(404)
            ->assertJson([
                'message' => '01 invoice not found!!'
            ]);
    }

    public function testUpdateInvoiceValidationRequired()
    {
        $payload = $this->mappingPayloadInvoice();

        $invoice = $this->postJson('/api/invoice', [
            'code' => $payload['code'],
            'subject' => $payload['subject'],
            'issue_date' => $payload['issue_date'],
            'due_date' => $payload['due_date'],
            'customer_id' => $payload['customer_id'],
            'products' => $payload['products'],
        ]);

        $code = json_decode($invoice->getContent())->data->code;

        $this->json('put', 'api/invoice/' . $code, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors([
                'subject',
                'issue_date',
                'due_date',
                'customer_id',
                'products'
            ]);
    }

    public function testUpdateInvoiceSuccess()
    {
        $payload = $this->mappingPayloadInvoice();

        $invoice = $this->postJson('/api/invoice', [
            'code' => $payload['code'],
            'subject' => $payload['subject'],
            'issue_date' => $payload['issue_date'],
            'due_date' => $payload['due_date'],
            'customer_id' => $payload['customer_id'],
            'products' => $payload['products'],
        ]);

        $code = json_decode($invoice->getContent())->data->code;

        $response = $this->json('put', 'api/invoice/' . $code, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Invoice update successfully',
                'data' => $payload,
            ]);
    }

    public function mappingPayloadInvoice(): array
    {
        $attributeProducts = $this->mappingAttributeProduct();

        $products = Product::factory(3)->create([
            "name" => $this->faker->word(),
            "type" => $attributeProducts['type'],
            "qty" => $attributeProducts['qty'],
            "unit_price" => $attributeProducts['unit_price'],
            "total_price" => $attributeProducts['total_price']
        ]);

        return  [
            'code' => '00001',
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'customer_id' => Customer::factory()->create()->id,
            'products' => [
                [
                    'product_id' => $products[0]->id,
                    'product_name' => $products[0]->name,
                    'qty' => $products[0]->qty,
                    'unit_price' => $products[0]->unit_price,
                ],
                [
                    'product_id' => $products[2]->id,
                    'product_name' => $products[2]->name,
                    'qty' => $products[2]->qty,
                    'unit_price' => $products[2]->unit_price,
                ],
            ],
        ];
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
