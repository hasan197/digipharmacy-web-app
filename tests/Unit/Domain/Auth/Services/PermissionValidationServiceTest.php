<?php

namespace Tests\Unit\Domain\Auth\Services;

use App\Domain\Auth\Services\PermissionValidationService;
use PHPUnit\Framework\TestCase;

class PermissionValidationServiceTest extends TestCase
{
    private PermissionValidationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PermissionValidationService();
    }

    /** @test */
    public function it_should_throw_exception_for_invalid_domain()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid feature domain or action: invalid-domain.view');

        $this->service->getFeaturePermissions('invalid-domain', 'view');
    }

    /** @test */
    public function it_should_throw_exception_for_invalid_action()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid feature domain or action: role.invalid-action');

        $this->service->getFeaturePermissions('role', 'invalid-action');
    }

    /** @test */
    public function it_should_return_correct_permissions_for_valid_domain_and_action()
    {
        $permissions = $this->service->getFeaturePermissions('role', 'view');

        $this->assertEquals(['role.view'], $permissions);
    }

} 