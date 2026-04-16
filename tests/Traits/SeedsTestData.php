<?php

namespace Tests\Traits;

use App\Models\User;
use Database\Seeders\ConstantTypeSeeder;
use Database\Seeders\UserSeeder;
use Database\Seeders\PermissionSeeder;
use Database\Seeders\QuranSeeder;
use Spatie\Permission\Models\Role;

trait SeedsTestData
{
    protected User $adminUser;
    protected User $scopedUser;
    protected Role $adminRole;

    protected function seedBaseData(): void
    {
        $this->seed(ConstantTypeSeeder::class);
        $this->seed(UserSeeder::class);
        $this->seed(PermissionSeeder::class);
        // $this->seed(QuranSeeder::class);

        $this->adminUser = User::find(1);
    }
}
