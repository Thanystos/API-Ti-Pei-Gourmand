<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;

class UserTokenGenerateEvent extends Event
{
    public const NAME = 'user.token.generate';
    protected $user;
    protected $token;

    public function __construct(User $user)
    {
        $this->user = $user;
        $this->token = null;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): void
    {
        $this->token = $token;
    }
}
