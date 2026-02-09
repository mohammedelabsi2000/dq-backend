<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class AuditObserver
{
    public function creating(Model $model)
    {
        if (Auth::check()) {
            if (in_array('created_by', $model->getFillable())) {
                $model->created_by = Auth::id();
            }
            if (in_array('updated_by', $model->getFillable())) {
                $model->updated_by = Auth::id();
            }
        }
    }

    public function updating(Model $model)
    {
        if (Auth::check() && in_array('updated_by', $model->getFillable())) {
            $model->updated_by = Auth::id();
        }
    }

    public function deleting(Model $model)
    {
        if (Auth::check() && in_array('deleted_by', $model->getFillable())) {
            $model->deleted_by = Auth::id();
            $model->save();
        }
    }
}
