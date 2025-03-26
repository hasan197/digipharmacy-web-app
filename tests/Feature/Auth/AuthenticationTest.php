<?php

namespace Tests\Feature\Auth;

use App\Domain\Auth\Models\User as DomainUser;
use Illuminate\Support\Facades\Hash;
use App\Models\User as EloquentUser;
use App\Infrastructure\Auth\Mappers\UserMapper;
use App\Infrastructure\Auth\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private UserMapper $userMapper;
    private UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userMapper = $this->app->make(UserMapper::class);
        $this->userRepository = $this->app->make(UserRepository::class);
    }

    public function test_users_can_login_with_valid_credentials()
    {
        // Create domain user
        $domainUser = new DomainUser(
            name: 'Test User',
            email: 'test@example.com',
            password: Hash::make('password123')
        );

        // Save user using repository
        $this->userRepository->save($domainUser);

        // Verify domain user can verify password
        $this->assertTrue($domainUser->verifyPassword('password123'));

        // Test API endpoint
        $response = $this->post('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'password123'
        ]);

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

        $this->assertAuthenticated();
    }

    public function test_users_cannot_login_with_invalid_password()
    {
        // Create domain user
        $domainUser = new DomainUser(
            name: 'Test User',
            email: 'test@example.com',
            password: Hash::make('password123')
        );

        // Save user using repository
        $this->userRepository->save($domainUser);

        // Verify domain user rejects wrong password
        $this->assertFalse($domainUser->verifyPassword('wrongpassword'));

        // Test API endpoint
        $response = $this->post('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);

        $this->assertGuest();
    }

    public function test_users_cannot_login_with_nonexistent_email()
    {
        $response = $this->post('/api/auth/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'password123'
        ]);

        $response->assertStatus(401)
            ->assertJson([
                'error' => 'Unauthorized'
            ]);

        $this->assertGuest();
    }

    public function test_users_can_logout()
    {
        // Create and save domain user
        $domainUser = new DomainUser(
            name: 'Test User',
            email: 'test@example.com',
            password: Hash::make('password123')
        );
        $this->userRepository->save($domainUser);
        
        $loginResponse = $this->post('/api/auth/login', [
            'email' => $domainUser->getEmail(),
            'password' => 'password123'
        ]);
        
        $token = $loginResponse->json()['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->post('/api/auth/logout');

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'User successfully signed out'
            ]);

        $this->assertGuest('api');
    }

    public function test_users_cannot_access_protected_routes_without_token()
    {
        $response = $this->getJson('/api/auth/user-profile');

        $response->assertStatus(401)
            ->assertJson([
                'message' => 'Unauthenticated.'
            ]);
    }

    public function test_users_can_access_protected_routes_with_valid_token()
    {
        // Create and save domain user
        $domainUser = new DomainUser(
            name: 'Test User',
            email: 'test@example.com',
            password: Hash::make('password123')
        );
        $this->userRepository->save($domainUser);
        
        $loginResponse = $this->post('/api/auth/login', [
            'email' => $domainUser->getEmail(),
            'password' => 'password123'
        ]);
        
        $token = $loginResponse->json()['access_token'];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token
        ])->getJson('/api/auth/user-profile');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'email',
                'created_at',
                'updated_at'
            ]);
    }

    public function test_login_validation_requires_email_and_password()
    {
        $response = $this->postJson('/api/auth/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_login_validation_requires_valid_email_format()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'invalid-email',
            'password' => 'password123'
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }
}
