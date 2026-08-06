<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckTokenIdleTimeout
{
    public function handle(Request $request, Closure $next): Response
    {
        /* $token = $request->user()?->currentAccessToken();

        if ($token && $token->last_used_at) {
            if ($token->last_used_at->lt(now()->subMinutes(60))) {
                $token->delete();

                return response()->json([
                    'message' => 'Session expired.'
                ], 401);
            }
        } */

        return $next($request);
    }
}