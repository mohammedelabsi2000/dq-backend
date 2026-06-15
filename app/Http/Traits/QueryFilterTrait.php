<?php

namespace App\Http\Traits;

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

        // Search
        // يوجد trait منفصل للبحث لكن هذا trait عام ويحتوي على كل الفلاتر بما فيها البحث， لذلك تم دمج الكود الخاص بالبحث هنا
        $search = $options['search'] ?? request()->get('search');
        $searchColumns = $options['searchColumns'] ?? [];

        if ($search && !empty($searchColumns)) {
            $query = $query->where(function ($q) use ($search, $searchColumns) {
                foreach ($searchColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        // Count total before applying limit
        $count = $query->count(); // مهم لحساب العدد الكلي

        // Order
        $orderBy = $options['orderBy'] ?? request()->get('order_by');
        $orderColumn = $options['orderColumn'] ?? 'created_at';

        if ($orderBy) {
            $orderBy = strtolower($orderBy) === 'asec' ? 'asc' : $orderBy;
            $query = $query->orderBy($orderColumn, $orderBy);
        }

        // Pagination (skip/limit)
        $skip = $options['skip'] ?? request()->get('skip', 0);
        $limit = request()->get('limit');

        if ($limit === null || $limit === '') {
            $limit = $options['limit'] ?? 10;
        }

        if ($limit != '*') {
            $query = $query->skip($skip)->take($limit);
        }

        // Return modified query and pagination info
        return [
            'query' => $query,
            'skip' => $skip,
            'limit' => $limit,
            'count' => $count,
            'total' => $count, // for backward compatibility
        ];
    }

    /**
     * Apply filters and return array format for legacy compatibility
     * The difference between this method and applyFilters is that this method returns an array with the query and pagination info,
     * while applyFilters returns the modified query builder instance.
     * This is useful for backward compatibility with existing code that expects an array format.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param array $options
     * @return array
     */
    public function applyFiltersA($query, array $options = [])
    {
        $data = $this->applyFilters($query, $options);
        return [
            $data['query'],
            $data['skip'],
            $data['limit'],
            $data['count'],
        ];
    }
}
