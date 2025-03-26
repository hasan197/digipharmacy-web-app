<?php

namespace Tests\Unit\Domain\Auth\Services;

use App\Domain\Auth\Models\Credentials;
use App\Domain\Auth\Models\User;
use App\Domain\Auth\Repositories\UserRepositoryInterface;
use App\Domain\Auth\Services\AuthenticationService;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthenticationServiceTest extends TestCase
{
    private UserRepositoryInterface $userRepository;
    private AuthenticationService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->authService = new AuthenticationService($this->userRepository);
    }

    public function test_authenticate_with_valid_credentials_returns_success()
    {
        // Arrange
        $email = 'test@example.com';
        $password = 'password123';
        $hashedPassword = Hash::make($password);
        
        $user = new User(
            name: 'Test User',
            email: $email,
            password: $hashedPassword
        );

        $credentials = new Credentials(
            email: $email,
            password: $password,
            ipAddress: '127.0.0.1'
        );

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->authService->authenticate($credentials);

        // Assert
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('token', $result);
        $this->assertEquals('Bearer', $result['token_type']);
        $this->assertArrayHasKey('expires_in', $result);
        $this->assertSame($user, $result['user']);
    }

    public function test_authenticate_with_invalid_email_returns_failure()
    {
        // Arrange
        $credentials = new Credentials(
            email: 'nonexistent@example.com',
            password: 'password123',
            ipAddress: '127.0.0.1'
        );

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($credentials->getEmail())
            ->once()
            ->andReturnNull();

        // Act
        $result = $this->authService->authenticate($credentials);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid credentials', $result['message']);
        $this->assertEquals(401, $result['status']);
    }

    public function test_authenticate_with_invalid_password_returns_failure()
    {
        // Arrange
        $email = 'test@example.com';
        $wrongPassword = 'wrongpassword';
        $hashedPassword = Hash::make('correctpassword');
        
        $user = new User(
            name: 'Test User',
            email: $email,
            password: $hashedPassword
        );

        $credentials = new Credentials(
            email: $email,
            password: $wrongPassword,
            ipAddress: '127.0.0.1'
        );

        $this->userRepository
            ->shouldReceive('findByEmail')
            ->with($email)
            ->once()
            ->andReturn($user);

        // Act
        $result = $this->authService->authenticate($credentials);

        // Assert
        $this->assertFalse($result['success']);
        $this->assertEquals('Invalid credentials', $result['message']);
        $this->assertEquals(401, $result['status']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
