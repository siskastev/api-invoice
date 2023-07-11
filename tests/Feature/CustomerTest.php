<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerTest extends TestCase
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

    public function testCreateCustomersValidationRequired()
    {
        $response = $this->postJson('/api/customers', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'name',
                'address',
                'country',
                'city',
            ]);
    }

    public function testCreateCustomersSuccess()
    {
        $customer = [
            'name' => $this->faker->name,
            'address' => $this->faker->address,
            'country' => $this->faker->country,
            'city' => $this->faker->city
        ];

        $response = $this->postJson('/api/customers', $customer);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Customer created successfully',
                'data' => $customer,
            ]);
    }

    public function testCustomersNotFound()
    {
        $response = $this->getJson('/api/customers/001');

        $response->assertStatus(404)
            ->assertJson([
                'message' => '001 customer not found!!'
            ]);
    }

    public function testUpdateCustomersSuccess()
    {
        $customer = Customer::factory()->create();

        $payload = [
            'name' => $this->faker->name,
            'address' => $customer->address,
            'country' => $customer->country,
            'city' => $customer->city
        ];

        $response = $this->putJson('/api/customers/' . $customer->id, $payload);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Customer update successfully',
                'data' => $payload
            ]);
    }
}
