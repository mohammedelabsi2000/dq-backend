<?php

namespace App\Listeners;

use Laravel\Sanctum\Events\TokenAuthenticated;
use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidateIdleAccessToken
{
    public const IDLE_TIMEOUT_MINUTES = 60;

    /**
     * Fires before Sanctum updates the token's last_used_at, so this still
     * sees the value from the previous request.
     */
    public function handle(TokenAuthenticated $event): void
    {
        $token = $event->token;

        if ($token->last_used_at && $token->last_used_at->lt(now()->subMinutes(self::IDLE_TIMEOUT_MINUTES))) {
            $token->delete();

            throw new HttpException(401, 'Session expired.');
        }
    }
}
