<?php

namespace App\Exceptions;

use App\Http\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
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
            return $this->error(
                "لا يوجد أي {$modelName} بالمعرّف المحدد",
                404
            );
        }

        // Authorization exception
        if ($e instanceof AuthorizationException) {
            return $this->error('ليس لديك صلاحية للقيام بهذا الإجراء.', 403);
        }

        // Route not found
        if ($e instanceof NotFoundHttpException) {
            return $this->error('الرابط المطلوب غير موجود.', 404);
        }

        // Authentication exception
        if ($e instanceof AuthenticationException) {
            return $this->error('غير مسجل دخول', 401);
        }

        // Validation exception
        if ($e instanceof ValidationException) {
            $errors = $e->validator->errors()->first();
            return $this->error($errors, 422);
        }

        // General HTTP exception
        if ($e instanceof HttpException) {
            return $this->error($e->getMessage(), $e->getStatusCode());
        }

        // Debug mode: show full exception
        if (config('app.debug')) {
            return parent::render($request, $e);
        }

        if ($e instanceof QueryException) {
            return $this->error('حدث خطأ في استعلام قاعدة البيانات', 500);
        } else if ($e instanceof \PDOException) {
            return $this->error('حدث خطأ في قاعدة البيانات', 500);
        }

        // Default fallback
        return $this->error('حدث خطأ غير متوقع', 500);
    }
}
