<?php

namespace App\Service;

use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Bundle\SecurityBundle\Security;

class UserTokenGeneratorService
{
    private $jwtManager;
    private $security;


    public function __construct(JWTManager $jwtManager, Security $security)
    {
        $this->jwtManager = $jwtManager;
        $this->security = $security;
    }

    public function generateUserToken($user): ?string
    {
        // Vérification si l'utilisateur connecté est celui qui est en train d'être modifié
        $loggedInUser = $this->security->getUser();
        $isConnected = ($loggedInUser && $loggedInUser->getUserIdentifier() === $user->getUsername());

        // Génération d'un nouveau token d'authentification si nécessaire
        return $isConnected ? $this->jwtManager->create($loggedInUser) : null;
    }
}
