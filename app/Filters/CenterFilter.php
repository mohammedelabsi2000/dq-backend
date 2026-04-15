<?php

namespace App\Filters;

use App\Filters\Traits\CommonFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class CenterFilter extends BaseFilter
{
    use CommonFilters;

    public function __construct(Builder|QueryBuilder $query, Request $request)
    {
        parent::__construct($query, $request);
    }

    public function apply(): Builder|QueryBuilder
    {
        // Apply common location filters
        $query = $this->applyLocationFilters($this->query, $this->request);
        
        // Apply center-specific filters
        $query = $this->applyBooleanFilters([
            'with_relations' => 'loadRelations'
        ]);
        
        return $query;
    }

    protected function loadRelations(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->with(['region.branch', 'mosque']);
    }
}
