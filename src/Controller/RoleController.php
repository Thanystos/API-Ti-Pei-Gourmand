<?php

namespace App\Controller;

use App\Entity\Role;
use App\Service\EntityCreatorService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\EntityUpdaterService;
use Symfony\Component\HttpFoundation\JsonResponse;

class RoleController
{
    private $request;
    private $entityUpdater;
    private $entityCreator;

    public function __construct(
        Request $request,
        EntityUpdaterService $entityUpdater,
        EntityCreatorService $entityCreator
    ) {
        $this->request = $request;
        $this->entityUpdater = $entityUpdater;
        $this->entityCreator = $entityCreator;
    }

    // Crée de nouvelles associations entre un User et un ou plusieurs rôles
    #[Route('/api/roles/{id}', name: 'update_role', methods: ['PUT'])]
    public function updateRole($id): JsonResponse
    {

        // Utilisation de mon service pour mettre à jour mon Role
        return $this->entityUpdater->updateEntity(
            $this->request,
            Role::class,
            ['role:read'],
            ['role:write'],
            [],
            false,
            $id
        );
    }

    // Crée de nouvelles associations entre un User et un ou plusieurs rôles
    #[Route('/api/roles/', name: 'register_role', methods: ['POST'])]
    public function registerRole(): JsonResponse
    {

        // Utilisation de mon service pour mettre à jour mon Role
        return $this->entityCreator->createEntity($this->request, Role::class, ['role:read'], ['role:write']);
    }
}
