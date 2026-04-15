<?php

namespace App\Policies;

use App\Models\HalaqaStudent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HalaqaStudentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('halaqa_students.show');
    }

    public function view(User $user, HalaqaStudent $halaqaStudent): bool
    {
        return $user->hasPermissionTo('halaqa_students.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('halaqa_students.create');
    }

    public function update(User $user, HalaqaStudent $halaqaStudent): bool
    {
        return $user->hasPermissionTo('halaqa_students.update');
    }

    public function delete(User $user, HalaqaStudent $halaqaStudent): bool
    {
        return $user->hasPermissionTo('halaqa_students.delete');
    }
}
