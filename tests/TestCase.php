<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Tests\Traits\SeedsTestData;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication, SeedsTestData;

    protected function actingAsAdmin(): self
    {
        return $this->actingAs($this->adminUser, 'sanctum');
    }
}
