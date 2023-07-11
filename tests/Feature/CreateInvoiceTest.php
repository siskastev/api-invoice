<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CreateInvoiceTest extends TestCase
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

    public function testCreateInvoiceValidationRequired()
    {
        $response = $this->postJson('/api/invoice', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'subject',
                'issue_date',
                'due_date',
                'customer_id',
                'products'
            ]);
    }

    public function testCreateInvoiceValidationIssueDateInvalid()
    {
        $payload = [
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('d-m-Y'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'customer_id' => Customer::factory()->create()->id,
            'products' => [],
        ];

        $response = $this->postJson('/api/invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['issue_date']);
    }

    public function testCreateInvoiceValidationDueDateInvalid()
    {
        $payload = [
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->format('d-m-Y'),
            'customer_id' => Customer::factory()->create()->id,
            'products' => [],
        ];

        $response = $this->postJson('/api/invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    public function testCreateInvoiceValidationDueDateBackDate()
    {
        $payload = [
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => date_create('2022-01-01')->format('d-m-Y'),
            'customer_id' => Customer::factory()->create()->id,
            'products' => [],
        ];

        $response = $this->postJson('/api/invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['due_date']);
    }

    public function testCreateInvoiceValidationCustomerInvalid()
    {
        $payload = [
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'customer_id' => '1234',
            'products' => [],
        ];

        $response = $this->postJson('/api/invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['customer_id']);
    }

    public function testCreateInvoiceValidationProductsInvalid()
    {
        $payload = [
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'customer_id' => Customer::factory()->create()->id,
            'products' => [
                [
                    "product_id" => 12345,
                    "product_name" => "Printer",
                    "qty" => 10,
                    "unit_price" => 100
                ]
            ],
        ];

        $response = $this->postJson('/api/invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.product_id', 'products.0.product_name']);
    }

    public function testCreateInvoiceValidationProductsNameInvalid()
    {

        $attribute = $this->mappingAttributeProduct();

        $product = Product::factory()->create([
            "name" => 'Product 1',
            "type" => $attribute['type'],
            "qty" => $attribute['qty'],
            "unit_price" => $attribute['unit_price'],
            "total_price" => $attribute['total_price']
        ]);

        $payload = [
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'customer_id' => Customer::factory()->create()->id,
            'products' => [
                [
                    "product_id" => $product->id,
                    "product_name" => "Printer",
                    "qty" => $product->qty,
                    "unit_price" => $product->total_price
                ]
            ],
        ];

        $response = $this->postJson('/api/invoice', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['products.0.product_name']);
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

    public function testCreateInvoiceSuccess()
    {
        $attributeProducts = $this->mappingAttributeProduct();

        $products = Product::factory(3)->create([
            "name" => 'product 1',
            "type" => $attributeProducts['type'],
            "qty" => $attributeProducts['qty'],
            "unit_price" => $attributeProducts['unit_price'],
            "total_price" => $attributeProducts['total_price']
        ]);

        $invoiceData = [
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

        $response = $this->postJson('/api/invoice', $invoiceData);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Invoice stored successfully',
                'data' => $invoiceData,
            ]);
    }

    public function testMappingProducts()
    {
        $attributeProducts = $this->mappingAttributeProduct();

        $products = Product::factory(2)->create([
            "name" => 'product 1',
            "type" => $attributeProducts['type'],
            "qty" => $attributeProducts['qty'],
            "unit_price" => $attributeProducts['unit_price'],
            "total_price" => $attributeProducts['total_price']
        ]);

        $payload = [
            'code' => '0001',
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'customer_id' => Customer::factory()->create()->id,
            'products' => [
                [
                    'product_id' => $products[0]->id,
                    'product_name' => $products[0]->name,
                    'qty' => 5,
                    'unit_price' => 1000,
                ],
                [
                    'product_id' => $products[1]->id,
                    'product_name' => $products[1]->name,
                    'qty' => 3,
                    'unit_price' => 1500,
                ],
            ],
        ];

        $mappedProducts = $this->mappingProducts($payload);

        $this->assertEquals(count($payload['products']), count($mappedProducts));
        for ($i = 0; $i < count($mappedProducts); $i++) {
            $this->assertEquals($payload['code'], $mappedProducts[$i]['invoice_code']);
            $this->assertEquals($payload['products'][$i]['product_id'], $mappedProducts[$i]['product_id']);
            $this->assertEquals($payload['products'][$i]['product_name'], $mappedProducts[$i]['product_name']);
            $this->assertEquals($payload['products'][$i]['qty'], $mappedProducts[$i]['qty']);
            $this->assertEquals($payload['products'][$i]['unit_price'], $mappedProducts[$i]['unit_price']);
            $this->assertEquals($payload['products'][$i]['qty'] * $payload['products'][$i]['unit_price'], $mappedProducts[$i]['total_price']);
        }

        $subtotal = $this->getSubTotal($payload['products']);
        $this->assertEquals($subtotal, array_sum(array_column($mappedProducts, 'total_price')));
    }

    public function testMappingInvoices()
    {
        $attribute = $this->mappingAttributeProduct();

        $product = Product::factory()->create([
            "name" => 'product 1',
            "type" => $attribute['type'],
            "qty" => $attribute['qty'],
            "unit_price" => $attribute['unit_price'],
            "total_price" => $attribute['total_price']
        ]);

        $payload = [
            'code' => '0001',
            'subject' => 'Invoice Subject',
            'issue_date' => now()->format('Y-m-d'),
            'due_date' => now()->addDays(7)->format('Y-m-d'),
            'customer_id' => Customer::factory()->create()->id,
            'products' => [
                [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'qty' => $product->qty,
                    'unit_price' => $product->unit_price
                ]
            ],
        ];

        $mappedInvoice = $this->mappingInvoices($payload);

        $this->assertEquals($payload['code'], $mappedInvoice['code']);
        $this->assertEquals($payload['subject'], $mappedInvoice['subject']);
        $this->assertEquals($payload['issue_date'], $mappedInvoice['issue_date']);
        $this->assertEquals($payload['due_date'], $mappedInvoice['due_date']);
        $this->assertEquals($payload['customer_id'], $mappedInvoice['customer_id']);
        $this->assertEquals(count($payload['products']), $mappedInvoice['total_items']);

        $subtotal = $this->getSubTotal($payload['products']);
        $taxAmount = $subtotal * Invoice::TAX_INVOICE;
        $this->assertEquals($subtotal + $taxAmount, $mappedInvoice['grand_total']);
    }

    private function mappingProducts(array $payloads): array
    {
        $data = [];

        foreach ($payloads['products'] as $value) {
            $data[] = [
                'invoice_code' => $payloads['code'],
                "product_id" => $value['product_id'],
                "product_name" => $value['product_name'],
                "qty" => $value['qty'],
                "unit_price" => $value['unit_price'],
                "total_price" => $value['qty'] * $value['unit_price'],
            ];
        }

        return $data;
    }

    private function mappingInvoices(array $payloads): array
    {
        $subTotal = $this->getSubTotal($payloads['products']);
        $taxAmount = $subTotal * Invoice::TAX_INVOICE;

        return [
            'code' => $payloads['code'],
            'status' => Invoice::STATUS_UNPAID,
            'subject' => $payloads['subject'],
            'issue_date' => $payloads['issue_date'],
            'due_date' => $payloads['due_date'],
            'customer_id' => $payloads['customer_id'],
            'total_items' => count($payloads['products']),
            'sub_total' => $subTotal,
            'tax' => $taxAmount,
            'grand_total' => $subTotal + $taxAmount,
        ];
    }

    private function getSubTotal(array $products): float
    {
        return array_sum(array_map(function ($product) {
            return $product['qty'] * $product['unit_price'];
        }, $products));
    }
}
