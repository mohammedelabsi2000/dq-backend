<?php

namespace App\Filters;

use App\Filters\Traits\CommonFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class HalaqaFilter extends BaseFilter
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
        
        // Apply halaqa-specific filters
        $query = $this->applyFilters([
            'reference_type' => 'filterByReferenceType',
            'type_id' => 'filterByTypeId',
        ]);
        
        return $query;
    }

    
    protected function filterByReferenceType(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('reference_type', $this->request->input('reference_type'));
    }

    protected function filterByTypeId(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('type_id', $this->request->integer('type_id'));
    }
}
