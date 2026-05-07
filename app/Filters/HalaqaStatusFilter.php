<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class HalaqaStatusFilter extends BaseFilter
{

    public function __construct(Builder|QueryBuilder $query, Request $request)
    {
        parent::__construct($query, $request);
    }

    public function apply(): Builder|QueryBuilder
    {
        // Apply halaqa-specific filters
        $query = $this->applyFilters([
            'halaqa_id' => 'filterByHalaqaId',
            'status_type_id' => 'filterByStatusTypeId',
            'sponsorship_type_id' => 'filterBySponsorshipTypeId',
            'from_date' => 'filterByFromDate',
            'to_date' => 'filterByToDate',
        ]);

        $query = $this->applyBooleanFilters([
            'active_only' => 'filterByActiveOnly'
        ]);

        return $query;
    }

    protected function filterByHalaqaId(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('halaqa_id', $this->request->integer('halaqa_id'));
    }

    protected function filterByStatusTypeId(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('status_type_id', $this->request->integer('status_type_id'));
    }

    protected function filterBySponsorshipTypeId(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('sponsorship_type_id', $this->request->integer('sponsorship_type_id'));
    }

    protected function filterByFromDate(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('from_date', '>=', $this->request->date('from_date'));
    }

    protected function filterByToDate(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where('to_date', '<=', $this->request->date('to_date'));
    }

    protected function filterByActiveOnly(Builder|QueryBuilder $query): Builder|QueryBuilder
    {
        return $query->where(function ($q) {
            $q->whereNull('to_date')
                ->orWhere('to_date', '>=', now());
        });
    }
}
