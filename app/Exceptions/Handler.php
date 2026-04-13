<?php

namespace App\Exceptions;

use App\Http\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * The list of inputs that are never flashed on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register exception handling callbacks.
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Render an exception into an HTTP response.
     */
    public function render($request, Throwable $e)
    {
        // Model not found
        if ($e instanceof ModelNotFoundException) {
            $modelName = strtolower(class_basename($e->getModel()));
            return $this->errorMessage(
                'لا يوجد ' . $modelName . ' بالمعرف المحدد',
                404
            );
        }

        // Authorization exception
        if ($e instanceof AuthorizationException) {
            // return $this->errorMessage($e->getMessage(), 403);
            return $this->errorMessage('ليس لديك صلاحية للقيام بهذا الإجراء', 403);
        }

        // Route not found
        if ($e instanceof NotFoundHttpException) {
            return $this->errorMessage('الرابط المطلوب غير موجود.', 404);
        }

        // Authentication exception
        if ($e instanceof AuthenticationException) {
            return $this->error('غير مسجل الدخول', 401);
        }

        // Validation exception
        if ($e instanceof ValidationException) {
            $errors = $e->validator->errors()->first();
            return $this->errorMessage($errors, 422);
        }

        // General HTTP exception
        if ($e instanceof HttpException) {
            return $this->errorMessage($e->getMessage(), $e->getStatusCode());
        }

        // Debug mode: show full exception
        if (config('app.debug')) {
            return parent::render($request, $e);
        }

        // Default fallback
        return $this->errorMessage('حدث خطأ غير متوقع', 500);
    }
}
