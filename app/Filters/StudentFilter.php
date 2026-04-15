<?php

namespace App\Filters;

use App\Filters\Traits\CommonFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class StudentFilter extends BaseFilter
{
    use CommonFilters;

    public function __construct(Builder|QueryBuilder $query, Request $request)
    {
        parent::__construct($query, $request);
    }

    /**
     * Apply all filters to the query
     * 
     * @return Builder
     */
    public function apply(): Builder|QueryBuilder
    {
        // Apply common location filters
        $query = $this->applyLocationFilters($this->query, $this->request);
        
        // Apply student-specific filters
        $query = $this->applyFilters([
            'halaqa_id' => 'filterByHalaqa',
            'gender' => 'filterByGender',
            'marital_status_id' => 'filterByMaritalStatus',
            'money_status_id' => 'filterByMoneyStatus',
            'guardian_type_id' => 'filterByGuardianType',
            'age_min' => 'filterByAgeMin',
            'age_max' => 'filterByAgeMax',
            'guardian_id' => 'filterByGuardianId'
        ]);
        
        // Apply boolean filters
        $query = $this->applyBooleanFilters([
            'has_halaqa' => 'filterByHasHalaqa'
        ]);
        
        return $query;
    }


    protected function filterByHalaqa(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        $halaqaId = $this->request->integer('halaqa_id');
        return $query->whereHas('halaqas', function ($q) use ($halaqaId) {
            $q->where('halaqa_id', $halaqaId);
        });
    }

    protected function filterByGender(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('gender', $this->request->input('gender'));
    }




    protected function filterByMaritalStatus(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('marital_status_id', $this->request->integer('marital_status_id'));
    }

    protected function filterByMoneyStatus(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('money_status_id', $this->request->integer('money_status_id'));
    }

    protected function filterByGuardianType(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('guardian_type_id', $this->request->integer('guardian_type_id'));
    }

    protected function filterByAgeMin(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('dob', '<=', now()->subYears($this->request->integer('age_min')));
    }

    protected function filterByAgeMax(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('dob', '>=', now()->subYears($this->request->integer('age_max') + 1));
    }

    protected function filterByHasHalaqa(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->whereHas('halaqas');
    }

    protected function filterByGuardianId(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('guardian_id', $this->request->input('guardian_id'));
    }
}
