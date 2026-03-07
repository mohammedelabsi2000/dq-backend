<?php

namespace App\Http\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponser
{
    /**
     * Success response with data
     * @param mixed $data
     * @param string $message
     * @param int $code
     * @return JsonResponse
     */
    protected function success($data, string $message = '', int $code = 200): JsonResponse
    {
        return response()->json([
            'status' => true,
            'message' => $message,
            'code' => $code,
            'data' => $data,
        ], $code);
    }

    /**
     * Error response
     * @param string $message
     * @param int $code
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function error($message = 'حدث خطأ', $code = 400, $errors = null): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'errors' => $errors
        ], $code);
    }

    /**
     * Validation error response
     * @param mixed $errors
     * @return JsonResponse
     */
    protected function validationError($errors): JsonResponse
    {
        return response()->json([
            'status' => false,
            'code' => 422,
            'message' => 'خطأ في التحقق من البيانات',
            'errors' => $errors
        ], 422);
    }

    /**
     * Not found response
     * @param string $message
     * @return JsonResponse
     */
    protected function notFound($message = 'العنصر غير موجود'): JsonResponse
    {
        return response()->json([
            'status' => false,
            'message' => $message
        ], 404);
    }

    /**
     * Success message only (no data)
     * @param string $msg
     * @param int $code
     * @return JsonResponse
     */
    protected function successMessage($msg, $code = 200): JsonResponse
    {
        return response()->json([
            'message' => $msg,
            'code' => $code,
            'success' => true,
        ], $code);
    }

    /**
     * Error message only (no data)
     * @param string $msg
     * @param int $code
     * @return JsonResponse
     */
    protected function errorMessage($msg, $code = 400): JsonResponse
    {
        return response()->json([
            'message' => $msg,
            'code' => $code,
            'success' => false,
        ], $code);
    }

    /**
     * Error response with errors array
     * @param mixed $data
     * @param int $code
     * @return JsonResponse
     */
    protected function errorResponse($data, $code = 400): JsonResponse
    {
        return response()->json([
            'code' => $code,
            'success' => false,
            'errors' => $data,
        ], $code);
    }

    /**
     * API Response - flexible response builder
     * @param mixed $data
     * @param string $msg
     * @param int $code
     * @param bool $success
     * @return JsonResponse
     */
    protected function apiResponse($data = null, $msg = '', $code = 200, $success = true): JsonResponse
    {
        $response = [
            'message' => $msg,
            'code' => $code,
            'success' => $success,
        ];

        if ($data !== null) {
            $response['data'] = $data;
        }

        return response()->json($response, $code);
    }

    /**
     * Paginate helper - format pagination data
     * @param mixed $object
     * @return array
     */
    protected function paginate($object): array
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
