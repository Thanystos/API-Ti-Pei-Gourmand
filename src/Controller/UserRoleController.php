<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserRole;
use App\Service\AssociativeEntityCreatorService;
use App\Service\AssociativeEntityDeleterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserRoleController
{
    private $logger;
    private $request;
    private $associativeEntityCreator;
    private $associativeEntityDeleter;

    public function __construct(LoggerInterface $logger, Request $request, AssociativeEntityCreatorService $associativeEntityCreator, AssociativeEntityDeleterService $associativeDeleter)
    {
        $this->logger = $logger;
        $this->request = $request;
        $this->associativeEntityCreator = $associativeEntityCreator;
        $this->associativeEntityDeleter = $associativeDeleter;
    }

    // Crée de nouvelles associations entre un User et un ou plusieurs rôles
    #[Route('/api/user_roles', name: 'register_userRoles', methods: ['POST'])]
    public function registerUserRoles(): JsonResponse
    {
        return $this->associativeEntityCreator->createAssociativeEntity($this->request, User::class, Role::class, UserRole::class);
    }

    // Crée une nouvelle association entre un User et un ou plusieurs rôles
    #[Route('/api/user_roles', name: 'delete_userRoles', methods: ['DELETE'])]
    public function deleteUserRoles(): JsonResponse
    {
        return $this->associativeEntityDeleter->deleteAssociativeEntity($this->request, User::class, Role::class, UserRole::class);
    }
}
