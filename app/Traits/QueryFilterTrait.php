<?php

namespace App\Traits;

trait QueryFilterTrait
{
    /**
     * Apply generic query filters: skip/limit, search, order
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $options
     *      - 'search' => string
     *      - 'searchColumns' => array
     *      - 'orderBy' => string (asc/desc)
     *      - 'orderColumn' => string
     *      - 'skip' => int
     *      - 'limit' => int
     * @return array
     */
    public function applyFilters($query, array $options = [])
    {
        // Pagination
        $skip = $options['skip'] ?? request()->get('skip', 0);


        $count = $query->count(); // مهم لحساب العدد الكلي

        $limit = request()->get('limit');

        if ($limit === null || $limit === '') {
            $limit = $options['limit'] ?? 10;
        }

        if ($limit != '*') {
            $query = $query->skip($skip)->take($limit);
        }

        // Order
        $orderBy = $options['orderBy'] ?? request()->get('order_by');
        $orderColumn = $options['orderColumn'] ?? 'created_at';

        if ($orderBy) {
            $orderBy = strtolower($orderBy) === 'asec' ? 'asc' : $orderBy;
            $query = $query->orderBy($orderColumn, $orderBy);
        }

        // Search
        $search = $options['search'] ?? request()->get('search');
        $searchColumns = $options['searchColumns'] ?? [];

        if ($search && !empty($searchColumns)) {
            $query = $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        return [
            'query' => $query,
            'skip' => $skip,
            'limit' => $limit,
            'count' => $count,
        ];
    }
}
