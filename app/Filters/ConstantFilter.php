<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class ConstantFilter extends BaseFilter
{
    public function __construct(Builder|QueryBuilder $query, Request $request)
    {
        parent::__construct($query, $request);
    }

    public function apply()
    {
        $query = $this->query;
        $request = $this->request;

        return $query
            ->when($request->isNotFilled('with_inactive'), fn(Builder|QueryBuilder $q) => $q->where('is_active', 1));
    }
}
