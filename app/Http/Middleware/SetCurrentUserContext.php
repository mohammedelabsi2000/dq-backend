<?php

namespace App\Http\Middleware;

use App\Support\CurrentUserContext;
use Closure;
use Illuminate\Http\Request;

class SetCurrentUserContext
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        app(CurrentUserContext::class)
            ->set($request->user());

        return $next($request);
    }
}
