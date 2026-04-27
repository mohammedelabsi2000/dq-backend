<?php

namespace App\Models;

use App\Concerns\Searchable;
use App\Http\Traits\QueryFilterTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DqModel extends Model
{
    use HasFactory, SoftDeletes, QueryFilterTrait, Searchable;

    // Read this property in AppServiceProvider to determine if audit should be enabled for this model
    public static $usesAudit = true;

    /**
     * Get the user that created the model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user that updated the model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the user that deleted the model.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function deleter()
    {
        return $this->belongsTo(User::class, 'deleted_by');
    }

    /**
     * Apply skip and limit to the query based on request parameters or provided values.
     * If 'limit' is '*', no pagination will be applied.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $skip Number of records to skip (default: from request 'skip' param, fallback to 0)
     * @param int|string|null $limit Number of records to take (default: from request 'limit' param, fallback to 10, use '*' for no limit)
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSkipAndLimit($query, $skip = null, $limit = null)
    {
        // Handle skip parameter
        $skip = $skip ?? request()->get('skip', 0);
        $query->skip($skip);

        // Handle limit parameter
        $limit = $limit ?? request()->get('limit', 10);

        // Apply limit unless it's '*' (no limit)
        if ($limit !== '*') {
            $query->take($limit);
        }

        return $query;
    }

    /**
     * Scope a query to only include records created by a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCreatedBy($query, $userId)
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include records updated by a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeLastUpdatedBy($query, $userId)
    {
        return $query->where('updated_by', $userId);
    }

    /**
     * Scope a query to only include records deleted by a specific user.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $userId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeDeletedBy($query, $userId)
    {
        return $query->withTrashed()->where('deleted_by', $userId);
    }
}
