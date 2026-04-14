<?php

namespace App\Http\Traits;

trait ApiResponser
{

    protected function success($data, string $message = '', int $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'code' => $code,
            'data' => $data,
        ], $code);
    }

    /**
     * Return success response with pagination data.
     *
     * @param mixed $data
     * @param array{
     *     total?: int|null,
     *     skip?: int|null,
     *     limit?: int|null
     * } $pagination
     * @param string $message
     * @param int $code
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successWithPagination($data, $pagination = [], $message = '', $code = 200)
    {
        $pag = array_merge($pagination, [
            'success' => true,
            'message' => $message,
            'code' => $code,
            'data' => $data,
        ]);

        return response()->json($pag, $code);
    }

    protected function error($message = 'حدث خطأ', $code = 400, $errors = null)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'code' => $code,
        ], $code);
    }

    protected function paginate($object)
    {
        return [
            'current_page' => $object->currentPage(),
            'last_page' => $object->lastPage(),
            'first_page_url' => $object->url(1),
            'last_page_url' => $object->url($object->lastPage()),
            'next_page_url' => $object->nextPageUrl(),
            'prev_page_url' => $object->previousPageUrl(),
            'from' => $object->firstItem(),
            'to' => $object->lastItem(),
            'per_page' => $object->perPage(),
            'total' => $object->total(),
            'skip' => $object->firstItem() - 1,
            'limit' => $object->perPage(),
        ];
    }
}
