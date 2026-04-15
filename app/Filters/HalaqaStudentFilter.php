<?php

namespace App\Filters;

use App\Filters\Traits\CommonFilters;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class HalaqaStudentFilter extends BaseFilter
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

        // Apply halaqa-students-specific filters
        $query = $this->applyFilters([
            'enrollment_status_id' => 'filterByEnrollmentStatusId',
            'student_id' => 'filterByStudentId',
            'search' => 'filterBySearch'
        ]);

        $query = $this->applyBooleanFilters([
            'active_only' => 'filterByActiveOnly'
        ]);

        return $query;
    }


    protected function filterByEnrollmentStatusId(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('enrollment_status_id', $this->request->integer('enrollment_status_id'));
    }

    protected function filterByStudentId(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('student_id', $this->request->integer('student_id'));
    }
    protected function filterBySearch(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        $search = $this->request->get('search');

        return $query->dqSearch($search, [], [
            'student' => ['full_name'],
        ]);
    }
    

    protected function filterByActiveOnly(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where(function ($q) {
            $q->whereNull('to_date')
                ->orWhere('to_date', '>=', now());
        });
    }
}
