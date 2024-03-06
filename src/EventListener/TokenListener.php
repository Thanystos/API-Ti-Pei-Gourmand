<?php

namespace App\EventListener;

use App\Event\UserTokenGenerateEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;

class TokenListener
{
    private $jwtManager;

    public function __construct(JWTManager $jwtManager)
    {
        $this->jwtManager = $jwtManager;
    }

    public function onUserTokenGenerate(UserTokenGenerateEvent $event)
    {
        $user = $event->getUser();

        $token = $this->jwtManager->create($user);

        $event->setToken($token);
    }
}
