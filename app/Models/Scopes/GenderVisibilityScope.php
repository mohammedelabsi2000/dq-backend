<?php

namespace App\Models\Scopes;

use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Student;
use App\Models\User;
use App\Support\CurrentUserContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class GenderVisibilityScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $builder
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function apply(Builder $builder, Model $model)
    {
        $user = app(CurrentUserContext::class)->user();

        if ($user) {
            $isGlobalAdmin = !$user->can('gender_visibility');

            if (
                $isGlobalAdmin && (
                    $model instanceof User
                    || $model instanceof Student
                    || $model instanceof Center
                    || $model instanceof Halaqa
                )
            ) {
                $builder->where('gender', $user->gender);
            }
        }
        $builder;
    }
}
