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
        return $user->hasPermissionTo('students.show')
            && $this->isVisible($user, $student);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('students.create');
    }

    public function update(User $user, Student $student): bool
    {
        return $user->hasPermissionTo('students.update')
            && $this->isVisible($user, $student);
    }

    public function delete(User $user, Student $student): bool
    {
        return $user->hasPermissionTo('students.delete')
            && $this->isVisible($user, $student);
    }

    private function isVisible(User $user, Student $student): bool
    {
        return Student::visibleTo($user)->where('id', $student->id)->exists();
    }
}
