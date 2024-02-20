<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Repository\UserRoleRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: UserRoleRepository::class)]
#[ApiResource(
    operations: [
        new Post(
            denormalizationContext: ['groups' => ['userRole:write']],
            name: 'register_userRoles',
            uriTemplate: '/user_roles',
            controller: 'App\Controller\UserRoleController::registerUserRoles'
        ),
        new GetCollection(
            normalizationContext: ['groups' => ['userRole:read']],
        ),
        new Delete(
            name: 'delete_userRoles',
            uriTemplate: '/user_roles',
            controller: 'App\Controller\UserRoleController::deleteUserRoles'
        )
    ]
)]
class UserRole
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]

    #[Groups(['user:read'])]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'userRoles')]
    #[Groups(['userRole:write', 'userRole:read'])]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'userRoles')]
    #[Groups(['user:read', 'userRole:write', 'userRole:read'])]
    private ?Role $role = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
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
}
