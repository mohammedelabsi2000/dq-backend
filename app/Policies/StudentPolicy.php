<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('students.show');
    }

    public function view(User $user, Student $student): bool
    {
        return $user->hasPermissionTo('students.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasPermissionTo('students.update');
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasPermissionTo('students.delete');
    }
}
