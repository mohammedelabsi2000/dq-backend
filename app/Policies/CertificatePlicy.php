<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CertificatePlicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('users.certificates.show')
            || $user->hasPermissionTo('students.certificates.show');
    }

    public function view(User $user)
    {
        return $user->hasPermissionTo('users.certificates.show')
            || $user->hasPermissionTo('students.certificates.show');
    }

    public function create(User $user)
    {
        return $user->hasPermissionTo('users.certificates.update')
            || $user->hasPermissionTo('students.certificates.update');
    }

    public function update(User $user)
    {
        return $user->hasPermissionTo('users.certificates.update')
            || $user->hasPermissionTo('students.certificates.update');
    }

    public function delete(User $user)
    {
        return $user->hasPermissionTo('users.certificates.update')
            || $user->hasPermissionTo('students.certificates.update');
    }

    public function restore(User $user)
    {
        //
    }

    public function forceDelete(User $user, Certificate $certificate)
    {
        //
    }
}
