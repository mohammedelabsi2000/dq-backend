<?php

namespace App\Policies;

use App\Models\DailyAchievement;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class DailyAchievementPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('daily_achievements.show');
    }

    public function view(User $user, DailyAchievement $dailyAchievement): bool
    {
        return $user->hasPermissionTo('daily_achievements.show')
            && DailyAchievement::where('id', $dailyAchievement->id)
                ->visibleTo($user)
                ->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('daily_achievements.create');
    }

    public function update(User $user, DailyAchievement $dailyAchievement): bool
    {
        return $user->hasPermissionTo('daily_achievements.update')
            && DailyAchievement::where('id', $dailyAchievement->id)
                ->visibleTo($user)
                ->exists();
    }

    public function delete(User $user, DailyAchievement $dailyAchievement): bool
    {
        return $user->hasPermissionTo('daily_achievements.delete')
            && DailyAchievement::where('id', $dailyAchievement->id)
                ->visibleTo($user)
                ->exists();
    }
}
