<?php

namespace App\Controller;

use App\Entity\Permission;
use App\Entity\Role;
use App\Entity\RolePermission;
use App\Service\AssociativeEntityCreatorService;
use App\Service\AssociativeEntityDeleterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RolePermissionController
{
    private $logger;
    private $request;
    private $associativeEntityCreator;
    private $associativeEntityDeleter;

    public function __construct(
        LoggerInterface $logger,
        Request $request,
        AssociativeEntityCreatorService $associativeEntityCreator,
        AssociativeEntityDeleterService $associativeDeleter
    ) {
        $this->logger = $logger;
        $this->request = $request;
        $this->associativeEntityCreator = $associativeEntityCreator;
        $this->associativeEntityDeleter = $associativeDeleter;
    }

    // Crée de nouvelles associations entre un User et un ou plusieurs rôles
    #[Route('/api/role_permissions', name: 'register_rolePermissions', methods: ['POST'])]
    public function registerRolePermissions(): JsonResponse
    {
        return $this->associativeEntityCreator->createAssociativeEntity(
            $this->request,
            Role::class,
            Permission::class,
            RolePermission::class
        );
    }

    // Supprime une ou plusieurs associations entre un Role et une permission
    #[Route('/api/role_permissions', name: 'delete_rolePermissions', methods: ['DELETE'])]
    public function deleteRolePermissions(): JsonResponse
    {
        return $this->associativeEntityDeleter->deleteAssociativeEntity(
            $this->request,
            Role::class,
            Permission::class,
            RolePermission::class
        );
    }
}
