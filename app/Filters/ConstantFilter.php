<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Request;

class ConstantFilter extends BaseFilter
{
    public function __construct(private Builder|QueryBuilder $query, private Request $request)
    {
    }

    public function apply()
    {
        /** @var Builder|QueryBuilder $query */
        /** @var Request $request */

        $query = $this->query;
        $request = $this->request;

        return $query
            ->when($request->isNotFilled('with_inactive'), fn(Builder|QueryBuilder $q) => $q->where('is_active', 1));
    }
}
