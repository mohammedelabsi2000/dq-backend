<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\StudentPlan;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class StudentPlanPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any student plans.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('student_plans.show');
    }

    /**
     * Determine whether the user can view a specific student plan.
     */
    public function view(User $user, StudentPlan $studentPlan): bool
    {
        if (!$user->hasPermissionTo('student_plans.show')) {
            return false;
        }

        // Check if user can view the student associated with this plan
        return Student::where('id', $studentPlan->student_id)
            ->visibleTo($user)
            ->exists();
    }

    /**
     * Determine whether the user can create student plans.
     */
    public function create(User $user): bool
    {
        return $user->hasPermissionTo('student_plans.create');
    }

    /**
     * Determine whether the user can enroll specific students in a plan.
     * This checks if all students are within the user's jurisdiction.
     */
    public function enrollStudents(User $user, array $studentIds): bool
    {
        if (!$user->hasPermissionTo('student_plans.create')) {
            return false;
        }

        // Global admin can enroll any student
        if ($user->isGlobalAdmin()) {
            return true;
        }

        // Check if all students are within the user's scope
        $visibleStudentCount = Student::whereIn('id', $studentIds)
            ->visibleTo($user)
            ->count();

        return $visibleStudentCount === count($studentIds);
    }

    /**
     * Determine whether the user can update a specific student plan.
     */
    public function update(User $user, StudentPlan $studentPlan): bool
    {
        if (!$user->hasPermissionTo('student_plans.update')) {
            return false;
        }

        // Check if user can manage the student associated with this plan
        return Student::where('id', $studentPlan->student_id)
            ->visibleTo($user)
            ->exists();
    }

    /**
     * Determine whether the user can delete a student plan.
     */
    public function delete(User $user, StudentPlan $studentPlan): bool
    {
        if (!$user->hasPermissionTo('student_plans.delete')) {
            return false;
        }

        // Check if user can manage the student associated with this plan
        return Student::where('id', $studentPlan->student_id)
            ->visibleTo($user)
            ->exists();
    }

    /**
     * Determine whether the user can set a plan as main.
     */
    public function setMain(User $user, StudentPlan $studentPlan): bool
    {
        return $this->update($user, $studentPlan);
    }

    /**
     * Determine whether the user can move a student to a new level.
     */
    public function moveLevel(User $user, StudentPlan $studentPlan): bool
    {
        return $this->update($user, $studentPlan);
    }

    /**
     * Determine whether the user can close a student plan.
     */
    public function close(User $user, StudentPlan $studentPlan): bool
    {
        return $this->update($user, $studentPlan);
    }
}
