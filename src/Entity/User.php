<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\HasLifecycleCallbacks]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['user:read']]
        ),
        new Put(
            denormalizationContext: ['groups' => ['user:update']],
            name: 'update_user',
            uriTemplate: '/users/{id}',
            controller: 'App\Controller\UserController::updateUser'
        ),
        new Post(
            denormalizationContext: ['groups' => ['user:write']],
            name: 'register_user',
            uriTemplate: '/users',
            controller: 'App\Controller\UserController::registerUser'
        ),
        new Delete(
            name: 'delete_user',
            uriTemplate: '/users',
            controller: 'App\Controller\UserController::deleteUser'
        )
    ]
)]

#[UniqueEntity('username', message: 'Ce pseudonyme est déjà utilisé.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;

    #[Assert\NotBlank(message: "Le pseudonyme doit être spécifié.")]
    #[ORM\Column(length: 180, unique: true)]
    #[Groups(['user:read', 'user:update', 'user:write'])]
    private ?string $username = null;

    #[Assert\NotBlank(message: "Le ou les rôles doivent être spécifiés.")]
    #[ORM\Column]
    #[Groups(['user:read', 'user:update', 'user:write'])]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[Assert\NotBlank(message: "Le mot de passe doit être spécifié.")]
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[Assert\NotBlank(message: "Le nom doit être spécifié.")]
    #[ORM\Column(length: 255, nullable: true, options: ["default" => ""])]
    #[Groups(['user:read', 'user:write'])]
    private ?string $realName = null;

    #[Assert\NotBlank(message: "Le numéro de téléphone doit être spécifié.")]
    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $phoneNumber = null;

    #[Assert\NotBlank(message: "L'email doit être spécifié.")]
    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[Assert\NotBlank(message: "La date d'embauche doit être spécifiée.")]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?\DateTimeInterface $hireDate = null;

    // Remove signifie que si le User est supprimé, l'image associée dans l'entité Image sera supprimée aussi
    #[ORM\OneToOne(inversedBy: 'user', cascade: ['persist', 'remove'])]
    private ?Image $userImage = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    /*public function getUsername(): ?string
    {
        return $this->username;
    }*/

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->username;
    }

    public function getUsername(): string
    {
        return $this->getUserIdentifier();
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        // Je retire le fait d'attribuer automatiquement ce role à un nouvel utilisateur
        //$roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getRealName(): ?string
    {
        return $this->realName;
    }

    public function setRealName(string $realName): static
    {
        $this->realName = $realName;

        return $this;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function setPhoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getHireDate(): ?\DateTimeInterface
    {
        return $this->hireDate;
    }

    public function setHireDate(?\DateTimeInterface $hireDate): static
    {
        $this->hireDate = $hireDate;

        return $this;
    }

    public function getUserImage(): ?Image
    {
        return $this->userImage;
    }

    public function setUserImage(?Image $userImage): static
    {
        $this->userImage = $userImage;

        return $this;
    }

    #[ORM\PrePersist]
    public function setDefaultsOnPersist()
    {
        // J'attribue l'imageName par défaut pour les nouveaux Users
        if ($this->userImage === null) {
            $this->userImage = new Image();
            $this->userImage->setImageName('%kernel.project_dir%/public/images/users/default_user_image.png');
        }
    }
}
