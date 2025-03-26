<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_login_with_valid_credentials()
    {
        // Arrange
        $password = 'password123';
        $user = User::factory()->create([
            'password' => bcrypt($password)
        ]);

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => $password
        ]);

        // Assert
        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user' => [
                    'id',
                    'name',
                    'email'
                ]
            ]);
    }

    public function test_user_cannot_login_with_invalid_email()
    {
        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials'
            ]);
    }

    public function test_user_cannot_login_with_invalid_password()
    {
        // Arrange
        $user = User::factory()->create([
            'password' => bcrypt('correctpassword')
        ]);

        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => $user->email,
            'password' => 'wrongpassword'
        ]);

        // Assert
        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Invalid credentials'
            ]);
    }

    public function test_user_cannot_login_with_invalid_input()
    {
        // Act
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => '123' // too short
        ]);

        // Assert
        $response->assertStatus(422)
            ->assertJsonValidationErrors([
                'email',
                'password'
            ]);
    }
}
