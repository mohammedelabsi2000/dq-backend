<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\SeedsTestData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, SeedsTestData, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Spatie's permission/role cache lives for the whole test process, but
        // RefreshDatabase rolls back each test's DB changes (while auto-increment
        // ids keep climbing) — without this, a later test can see stale cached
        // permission/role ids that no longer match the freshly-seeded rows,
        // causing intermittent PermissionDoesNotExist failures when the full
        // suite runs (never reproducible when a test file runs alone).
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    protected function actingAsAdmin(): self
    {
        return $this->actingAs($this->adminUser, 'sanctum');
    }
}
