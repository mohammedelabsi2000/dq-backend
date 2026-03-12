<?php

namespace App\Traits;

trait Searchable
{
    /**
     * Apply search filter to the query based on specified columns and relations.
     * To use this function, the model must use the Searchable trait and call the scopeSearch 'search' in the query.
     * @param mixed $query
     * @param mixed $search
     * @param array $columns
     * @param array $relations
     */
    public function scopeSearch($query, $search, array $columns = [], array $relations = [])
    {
        if (!$search) {
            return $query;
        }

        $query->where(function ($q) use ($search, $columns, $relations) {

            // Search in columns
            foreach ($columns as $column) {
                $q->orWhere($column, 'LIKE', "%{$search}%");
            }

            // Search in relations
            foreach ($relations as $relation => $relationColumns) {
                $q->orWhereHas($relation, function ($qr) use ($search, $relationColumns) {
                    foreach ($relationColumns as $column) {
                        $qr->orWhere($column, 'LIKE', "%{$search}%");
                    }
                });
            }

        });

        return $query;
    }
}