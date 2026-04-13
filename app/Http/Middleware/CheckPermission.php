<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Http\Traits\ApiResponser;

class CheckPermission
{
    use ApiResponser;
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        if (!$request->user() || !$request->user()->can($permission)) {
            return $this->errorMessage('غير مصرح - لا تملك الصلاحية للوصول إلى هذا الرابط', 403);
        }

        return $next($request);
    }
}
