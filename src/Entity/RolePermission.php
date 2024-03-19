<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Repository\RolePermissionRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: RolePermissionRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            name: 'register_rolePermissions',
            uriTemplate: '/role_permissions',
            controller: 'App\Controller\RolePermissionController::registerRolePermissions'
        ),
        new Delete(
            name: 'delete_rolePermissions',
            uriTemplate: '/role_permissions',
            controller: 'App\Controller\RolePermissionController::deleteRolePermissions'
        )
    ]
)]
class RolePermission
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['role:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'rolePermissions')]
    private ?Role $role = null;

    #[ORM\ManyToOne(inversedBy: 'rolePermissions')]
    #[Groups(['role:read'])]
    private ?Permission $permission = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRole(): ?Role
    {
        return $this->role;
    }

    public function setRole(?Role $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function getPermission(): ?Permission
    {
        return $this->permission;
    }

    public function setPermission(?Permission $permission): static
    {
        $this->permission = $permission;

        return $this;
    }
}
