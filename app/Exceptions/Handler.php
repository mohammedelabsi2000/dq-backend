<?php

namespace App\Exceptions;

use App\Http\Traits\ApiResponser;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\QueryException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponser;

    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
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
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

        // ✅ أضف هذا
        // $this->renderable(function (\Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException $e, $request) {
        //     if ($request->is('api/*') || $request->expectsJson()) {
        //         return $this->error(
        //             'ليس لديك صلاحية للقيام بهذا الإجراء',
        //             403,
        //         );
        //     }
        // });


            // when model nonexistent
            if ($e instanceof ModelNotFoundException) {
                $modelName = strtolower(class_basename($e->getModel()));
                return $this->errorMessage('Does not exists any' . $modelName . 'with the spicified identificator', 404);
            }
        });
    }

            if ($e instanceof AuthorizationException) {
                return $this->errorMessage($e->getMessage(), 403);
            }

            // when write nonexistent URL
            if ($e instanceof NotFoundHttpException) {
                return $this->errorMessage('The specified URL connot be found.', 404);
            }

            if ($exception instanceof AuthenticationException) {
                return $this->error('Unauthenticated', 401);
                // return $this->errorMessage('Unauthenticated', 401);
            }

            if ($exception instanceof AuthorizationException) {
                return $this->error($exception->getMessage(), 403);
            }

            if ($exception instanceof ValidationException) {
                $errors = $exception->validator->errors()->first();
                return $this->errorMessage($errors, 422);
                // $errors = $e->errors();
                // return $this->errorResponse($errors, 422);
            }


            // NotFoundHttpException → أي Route غير موجود
            if ($exception instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return $this->notFound();
            }

            // general http exception
            if ($e instanceof HttpException) {
                return $this->errorMessage($e->getMessage(), $e->getStatusCode());
            }

            /* 
             * AuthenticationException → لو حاولت تدخل على Route محمي بدون توكن أو بتوكن غير صالح
             * 401 Unauthorized → عندما لا يتم توفير بيانات الاعتماد أو تكون غير صحيحة.
             */
            // if ($exception instanceof AuthenticationException) {
            //     return $this->errorMessage('Token غير صالح أو منتهي الصلاحية', 401);
            // }
        }

        // if we turn on the debugbar
        if (config('app.debug')) {
            return parent::render($request, $e);
        }
    }
}
