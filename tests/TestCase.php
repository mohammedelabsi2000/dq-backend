<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\SeedsTestData;
use Illuminate\Foundation\Testing\RefreshDatabase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, SeedsTestData, RefreshDatabase;

    protected function actingAsAdmin(): self
    {
        return $this->actingAs($this->adminUser, 'sanctum');
    }
}
