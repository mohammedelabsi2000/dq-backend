<?php

namespace App\Observers;

use App\Support\CurrentUserContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/*
 * This observer automatically fills the created_by, updated_by, and deleted_by fields
 * for any model that uses the $usesAudit property set to true.
 * It listens to the creating, updating, deleting, and restoring events of the model.
 * Make sure to register this observer in the AppServiceProvider for the models you want to audit.
 */
class AuditObserver
{
    /**
     * Handle the Model "creating" event.
     * @param Model $model
     * @return void
     */
    public function creating(Model $model)
    {
        if (Auth::check()) {
            if ($model->isFillable('created_by')) {
                $model->created_by = app(CurrentUserContext::class)->user()?->id;
            }
            if ($model->isFillable('updated_by')) {
                $model->updated_by = app(CurrentUserContext::class)->user()?->id;
            }
        }
    }

    /**
     * Handle the Model "updating" event.
     * @param Model $model
     * @return void
     */
    public function updating(Model $model)
    {
        if (Auth::check() && $model->isFillable('updated_by')) {
            $model->updated_by = app(CurrentUserContext::class)->user()?->id;
        }
    }

    /**
     * Handle the Model "deleting" event.
     * @param Model $model
     * @return void
     */
    public function deleting(Model $model)
    {
        if (Auth::check() && $model->isFillable('deleted_by')) {
            $model->deleted_by = app(CurrentUserContext::class)->user()?->id;
            $model->save();
        }
    }

    /**
     * Handle the Model "restoring" event.
     * This will clear the deleted_by field when a soft-deleted model is restored.
     * @param Model $model
     * @return void
     */
    public function restoring(Model $model)
    {
        if (Auth::check() && $model->isFillable('deleted_by')) {
            $model->deleted_by = null;
            $model->save();
        }
    }
}
