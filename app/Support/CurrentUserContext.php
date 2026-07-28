<?php

namespace App\Support;

use App\Models\User;

class CurrentUserContext
{
    public ?User $user = null;

    public function set(?User $user): void
    {
        $this->user = $user;
    }

    public function user(): ?User
    {
        return $this->user;
    }
}