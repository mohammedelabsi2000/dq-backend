<?php

namespace App\Exceptions;

use App\Http\Traits\ApiResponser;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\QueryException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
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
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });


        $this->renderable(function (QueryException $e, $request) {
            if ($request->expectsJson()) {
                return $this->error(
                    $e->getMessage(),
                    500,
                    ['حدث خطأ في قاعدة البيانات'],
                );
            }
        });

        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return $this->error(
                    'ليس لديك صلاحية للقيام بهذا الإجراء',
                    403,
                );
            }
        });
    }

    public function render($request, Throwable $exception)
    {
        if ($request->is('api/*')) {

            // NotFoundHttpException → أي Route غير موجود
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return $this->notFound();
            }

            // ModelNotFoundException → لو استخدمت Route Model Binding ولم يجد السجل
            if ($exception instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->notFound();
            }

            /* 
             * AuthenticationException → لو حاولت تدخل على Route محمي بدون توكن أو بتوكن غير صالح
             * 401 Unauthorized → عندما لا يتم توفير بيانات الاعتماد أو تكون غير صحيحة.
             */
            if ($exception instanceof AuthenticationException) {
                return $this->errorMessage('Token غير صالح أو منتهي الصلاحية', 401);
            }
        }

        return parent::render($request, $exception);
    }
}
