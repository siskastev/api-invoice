<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function testRegisterValidationFailed()
    {
        $payload = [
            'name' => '',
            'email' => 'invalid_email',
            'password' => '',
        ];

        $response = $this->json('POST', '/api/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function testRegisterAlreadyExistEmail()
    {
        $existingUser = User::factory()->create();

        $payload = [
            'name' => $this->faker->name,
            'email' => $existingUser->email,
            'password' => 'password123',
        ];

        $response = $this->json('POST', '/api/register', $payload);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    public function testRegisterSuccess()
    {
        $payload = [
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'password' => 'password',
        ];

        $response = $this->json('POST', '/api/register', $payload);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'message',
                'data' => [
                    'name',
                    'email',
                    'role',
                    'id',
                    'created_at',
                    'updated_at',
                ],
                'access_token'
            ]);

        $this->assertArrayHasKey('access_token', $response->json());

        $this->assertDatabaseHas('users', ['email' => $payload['email']]);
    }

    public function testLoginFailed()
    {
        $existingUser = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $payload = [
            'email' => $existingUser->email,
            'password' => "wrong_password",
        ];

        $response = $this->json('POST', '/api/login', $payload);

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Bad credentials',
            ]);
    }

    public function testLoginSuccess()
    {
        $existingUser = User::factory()->create([
            'password' => bcrypt('password')
        ]);

        $payload = [
            'email' => $existingUser->email,
            'password' => "password",
        ];

        $response = $this->json('POST', '/api/login', $payload);

        $this->assertAuthenticated();

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Login successfully',
            ]);

        $this->assertArrayHasKey('access_token', $response->json());
    }

    public function testLogout()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->json('POST', '/api/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Logged out successfully',
            ]);
    }

    public function testProfileData()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->json('GET', '/api/profile', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);
        
        $response
            ->assertStatus(200)
            ->assertJson([
                'name' => $user->name,
                'email' => $user->email,
                'id' => $user->id
            ])
            ->assertJsonStructure([
                "id",
                "name",
                "email",
            "email_verified_at",
                "role",
                "created_at",
                "updated_at",
                "deleted_at",
            ]);
    }
}
