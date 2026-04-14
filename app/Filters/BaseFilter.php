<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

abstract class BaseFilter
{
    protected Builder|QueryBuilder $query;
    protected Request $request;

    public function __construct(Builder|QueryBuilder $query, Request $request)
    {
        $this->query = $query;
        $this->request = $request;
    }

    /**
     * Apply filters based on request parameters
     */
    protected function applyFilters(array $filterMap = []): Builder|QueryBuilder
    {
        $query = $this->query;
        
        foreach ($filterMap as $param => $method) {
            if ($this->request->filled($param)) {
                $query = $this->$method($query);
            }
        }
        
        return $query;
    }

    /**
     * Apply boolean filters
     */
    protected function applyBooleanFilters(array $filterMap = []): Builder|QueryBuilder
    {
        $query = $this->query;
        
        foreach ($filterMap as $param => $method) {
            if ($this->request->boolean($param)) {
                $query = $this->$method($query);
            }
        }
        
        return $query;
    }

    abstract public function apply();
}
