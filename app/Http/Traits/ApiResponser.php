<?php

namespace App\Http\Traits;

trait ApiResponser
{

    //***************************************mohammed************************* */
    protected function success($data, string $message = '', int $code = 200)
    {
        return response()->json([
            'status'  => true,
            'message' => $message,
            'code'    => $code,
            'data'    => $data,
        ], $code);
    }


    protected function error($message = 'حدث خطأ', $code = 400, $errors = null)
    {
        return response()->json([
            'status'  => false,
            'message' => $message,
            'errors'  => $errors
        ], $code);
    }

    protected function validationError($errors)
    {
        return response()->json([
            'status'  => false,
            'code' => "422",
            'message' => 'خطأ في التحقق من البيانات',
            'errors'  => $errors
        ], 422);
    }

    protected function notFound($message = 'العنصر غير موجود')
    {
        return response()->json([
            'status'  => false,
            'message' => $message
        ], 404);
    }
    // ****************************** mohammed *******************************
    protected function successMessage($msg, $code = 200)
    {
        return response()->json([
            'message' => $msg,
            'code' => $code,
            'success' => true,
        ]);
    }

    protected function errorMessage($msg, $code = 400)
    {
        return response()->json([
            'message' => $msg,
            'code' => $code,
            'success' => false,
        ]);
    }

    protected function errorResponse($data, $code)
    {
        return response()->json([
            'code' => $code,
            'success' => false,
            'errors' => $data,
        ]);
    }

    protected function apiResponse($data, $msg, $code = 200, $success = true)
    {
        $resp = array_merge([
            'message' => $msg,
            'code' => $code,
            'success' => $success,
            // 'data' => $data,
        ], $data);
        return response()->json($resp);
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
