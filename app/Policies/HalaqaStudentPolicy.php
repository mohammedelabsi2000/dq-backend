<?php

namespace App\Policies;

use App\Models\Halaqa;
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
        return $user->hasPermissionTo('halaqa_students.show')
            && $this->isVisible($user, $halaqaStudent);
    }

    public function create(User $user, ?int $halaqaId = null): bool
    {
        if (!$user->hasPermissionTo('halaqa_students.create')) {
            return false;
        }

        if ($user->isGlobalAdmin() || !$halaqaId) {
            return true;
        }

        return Halaqa::visibleTo($user)->where('id', $halaqaId)->exists();
    }

    public function update(User $user, HalaqaStudent $halaqaStudent): bool
    {
        return $user->hasPermissionTo('halaqa_students.update')
            && $this->isVisible($user, $halaqaStudent);
    }

    public function delete(User $user, HalaqaStudent $halaqaStudent): bool
    {
        return $user->hasPermissionTo('halaqa_students.delete')
            && $this->isVisible($user, $halaqaStudent);
    }

    private function isVisible(User $user, HalaqaStudent $halaqaStudent): bool
    {
        return HalaqaStudent::visibleTo($user)->where('id', $halaqaStudent->id)->exists();
    }
}
