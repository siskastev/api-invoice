<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RoleAuthTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    public function testUserAccessGetInvoice()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/invoice');

        $response->assertStatus(401)->assertJson(['message' => 'Unauthorized']);
    }

    public function testUserAccessGetProducts()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/products');

        $response->assertStatus(200)->assertJson(['messages' => 'OK']);
    }

    public function testUserAccessGetCustomers()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('GET', '/api/customers');

        $response->assertStatus(401)->assertJson(['message' => 'Unauthorized']);
    }

    public function testUserAccessCreateCustomers()
    {
        $user = User::factory()->create();

        Sanctum::actingAs($user);

        $response = $this->json('POST', '/api/customers', [Customer::factory()->create()]);

        $response->assertStatus(401)->assertJson(['message' => 'Unauthorized']);
    }

    public function testAdminAccessGetCustomers()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Sanctum::actingAs($admin);

        $response = $this->json('GET', '/api/customers');

        $response->assertStatus(200)->assertJson(['messages' => 'OK']);
    }

    public function testAdminAccessGetInvoice()
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        Sanctum::actingAs($admin);

        $response = $this->json('GET', '/api/invoice');

        $response->assertStatus(200)->assertJson(['messages' => 'OK']);
    }
}
