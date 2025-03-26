<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_change_password()
    {
        $user = User::factory()->create([
            'password' => Hash::make('oldpassword')
        ]);

        $loginResponse = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => 'oldpassword'
        ]);

        $token = $loginResponse->json()['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/auth/change-password', [
            'current_password' => 'oldpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Password successfully changed'
            ]);

        // Verify old password no longer works
        $oldLoginResponse = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => 'oldpassword'
        ]);
        $oldLoginResponse->assertStatus(401);

        // Verify new password works
        $newLoginResponse = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => 'newpassword123'
        ]);
        $newLoginResponse->assertStatus(200);
    }

    public function test_change_password_requires_current_password()
    {
        $user = User::factory()->create();
        
        $loginResponse = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $token = $loginResponse->json()['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/auth/change-password', [
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_change_password_requires_valid_current_password()
    {
        $user = User::factory()->create();
        
        $loginResponse = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $token = $loginResponse->json()['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/auth/change-password', [
            'current_password' => 'wrongpassword',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'newpassword123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['current_password']);
    }

    public function test_change_password_requires_new_password_confirmation()
    {
        $user = User::factory()->create();
        
        $loginResponse = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $token = $loginResponse->json()['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/auth/change-password', [
            'current_password' => 'password',
            'new_password' => 'newpassword123',
            'new_password_confirmation' => 'differentpassword'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }

    public function test_change_password_requires_minimum_password_length()
    {
        $user = User::factory()->create();
        
        $loginResponse = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => 'password'
        ]);

        $token = $loginResponse->json()['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/auth/change-password', [
            'current_password' => 'password',
            'new_password' => 'short',
            'new_password_confirmation' => 'short'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['new_password']);
    }
}
