<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['user:read']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['user:write']],
            normalizationContext: ['groups' => ['user:read']],
            name: 'update_user',
            uriTemplate: '/users/{id}',
            controller: 'App\Controller\UserController::updateUser'
        ),
        new Post(
            denormalizationContext: ['groups' => ['user:write']],
            normalizationContext: ['groups' => ['user:read']],
            name: 'register_user',
            uriTemplate: '/users',
            controller: 'App\Controller\UserController::registerUser'
        ),
        new Post(
            name: 'register_user_image',
            uriTemplate: '/users',
            controller: 'App\Controller\UserController::registerUserImage'
        ),
        new Delete(
            name: 'delete_user',
            uriTemplate: '/users',
            controller: 'App\Controller\UserController::deleteUser'
        )
    ]
)]
#[UniqueEntity('username', message: 'duplicateUsername', groups: ['register', 'update'])]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['user:read'])]
    private ?int $id = null;


    #[Assert\NotBlank(message: "emptyUsername", groups: ['register'])]
    #[ORM\Column(length: 180)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $username = null;

    /**
     * @var string The hashed password
     */
    #[Assert\NotBlank(message: "emptyPassword", groups: ['register'])]
    #[ORM\Column]
    #[Groups(['user:write'])]
    private ?string $password = null;

    #[Assert\NotBlank(message: "emptyRealName", groups: ['register'])]
    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $realName = null;

    #[Assert\NotBlank(message: "emptyPhoneNumber", groups: ['register'])]
    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $phoneNumber = null;

    #[Assert\NotBlank(message: "emptyEmail", groups: ['register'])]
    #[ORM\Column(length: 255)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?\DateTimeInterface $hireDate = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $employmentStatus = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $comments = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?\DateTimeInterface $endDate = null;

    #[ORM\Column(length: 20, nullable: true)]
    #[Groups(['user:read', 'user:write'])]
    private ?string $socialSecurityNumber = null;

    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserRole::class, cascade: ['remove'])]
    #[Groups(['user:read'])]
    private Collection $userRoles;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $imageName = null;

    public function __construct()
    {
        $this->userRoles = new ArrayCollection();
    }

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

    public function getEmploymentStatus(): ?string
    {
        return $this->employmentStatus;
    }

    public function setEmploymentStatus(?string $employmentStatus): static
    {
        $this->employmentStatus = $employmentStatus;

        return $this;
    }

    public function getComments(): ?string
    {
        return $this->comments;
    }

    public function setComments(?string $comments): static
    {
        $this->comments = $comments;

        return $this;
    }

    public function getEndDate(): ?\DateTimeInterface
    {
        return $this->endDate;
    }

    public function setEndDate(?\DateTimeInterface $endDate): static
    {
        $this->endDate = $endDate;

        return $this;
    }

    public function getSocialSecurityNumber(): ?string
    {
        return $this->socialSecurityNumber;
    }

    public function setSocialSecurityNumber(?string $socialSecurityNumber): static
    {
        $this->socialSecurityNumber = $socialSecurityNumber;

        return $this;
    }

    /**
     * @return Collection<int, UserRole>
     */
    public function getUserRoles(): Collection
    {
        return $this->userRoles;
    }

    public function addUserRole(UserRole $userRole): static
    {
        if (!$this->userRoles->contains($userRole)) {
            $this->userRoles->add($userRole);
            $userRole->setUser($this);
        }

        return $this;
    }

    public function removeUserRole(UserRole $userRole): static
    {
        if ($this->userRoles->removeElement($userRole)) {
            // set the owning side to null (unless already changed)
            if ($userRole->getUser() === $this) {
                $userRole->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {

        /* 
            La méthode map itère sur chaque élément de mon tableau userRoles.
            $userRole représente chaque entrée de userRoles, à partir de laquelle nous récupérons le nom du rôle qui y est contenu.
            La fonction fn(UserRole $userRole) => $userRole->getRole()->getName() extrait le nom du rôle pour chaque élément.
            En utilisant array_unique, nous nous assurons que les noms de rôles sont uniques dans le tableau résultant.
        */
        return array_unique($this->userRoles->map(fn (UserRole $userRole) => $userRole->getRole()->getName())->toArray());
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }
}
