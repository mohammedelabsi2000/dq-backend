<?php

namespace App\Policies;

use App\Models\LevelTrackSubject;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class LevelTrackSubjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('level_track_subjects.show');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, LevelTrackSubject $levelTrackSubject): bool
    {
        return $user->hasPermissionTo('level_track_subjects.show');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('level_track_subjects.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, LevelTrackSubject $levelTrackSubject): bool
    {
        return $user->hasPermissionTo('level_track_subjects.update');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, LevelTrackSubject $levelTrackSubject): bool
    {
        return $user->hasPermissionTo('level_track_subjects.delete');
    }
}
