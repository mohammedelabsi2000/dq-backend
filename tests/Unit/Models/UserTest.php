<?php

namespace Tests\Unit\Models;

use App\Enums\Gender;
use App\Models\Mosque;
use App\Models\User;
use App\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->seedBaseData();
    }

    public function test_gender_text_attribute()
    {
        $user = User::factory()->create();

        $this->assertEquals($user->gender?->label() ?? 'غير محدد', $user->gender_text);
    }

    public function test_password_is_hidden()
    {
        $user = User::factory()->create();
        $this->assertArrayNotHasKey('password', $user->toArray());
    }

    public function test_mosque_relationship()
    {
        $mosque = Mosque::factory()->create();
        $user = User::factory()->create([
            'mosque_id' => $mosque->id,
        ]);

        $this->assertInstanceOf(Mosque::class, $user->mosque);
    }

    public function test_roles_relationship()
    {
        $user = User::factory()->create();

        $role = Role::factory()->create();
        $user->assignRole($role);

        $this->assertInstanceOf(\Spatie\Permission\Models\Role::class, $user->roles->first());
    }
}
