<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: IngredientRepository::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['ingredient:read']],
        ),
        new Put(
            denormalizationContext: ['groups' => ['ingredient:write']],
            normalizationContext: ['groups' => ['ingredient:read']],
            name: 'update_ingredient',
            uriTemplate: '/ingredients/{id}',
            controller: 'App\Controller\IngredientController::updateIngredient'
        ),
        new Post(
            denormalizationContext: ['groups' => ['ingredient:write']],
            normalizationContext: ['groups' => ['ingredient:read']],
            name: 'register_ingredient',
            uriTemplate: '/ingredients',
            controller: 'App\Controller\IngredientController::registerIngredient'
        ),
        new Delete(
            name: 'delete_ingredient',
            uriTemplate: '/ingredients',
            controller: 'App\Controller\IngredientController::deleteIngredient'
        )
    ]
)]
#[UniqueEntity('title', message: 'duplicateTitle', groups: ['IngredientRegister', 'IngredientUpdate'])]
class Ingredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['ingredient:read'])]
    private ?int $id = null;

    #[Assert\NotBlank(message: "emptyTitle", groups: ['IngredientRegister'])]
    #[ORM\Column(length: 255)]
    #[Groups(['ingredient:read', 'ingredient:write'])]
    private ?string $title = null;

    #[Assert\NotBlank(message: "emptyQuantity", groups: ['IngredientRegister'])]
    #[ORM\Column]
    #[Groups(['ingredient:read', 'ingredient:write'])]
    private ?float $quantity = null;

    #[ORM\Column]
    #[Groups(['ingredient:read', 'ingredient:write'])]
    private ?bool $isAllergen = null;

    #[ORM\OneToMany(mappedBy: 'ingredient', targetEntity: DishIngredient::class)]
    private Collection $dishIngredients;

    #[Assert\NotBlank(message: "emptyCategory", groups: ['IngredientRegister'])]
    #[ORM\Column(length: 255)]
    #[Groups(['ingredient:read', 'ingredient:write'])]
    private ?string $category = null;

    #[Assert\NotBlank(message: "emptyUnit", groups: ['IngredientRegister'])]
    #[ORM\Column(length: 255)]
    #[Groups(['ingredient:read', 'ingredient:write'])]
    private ?string $unit = null;

    public function __construct()
    {
        $this->dishIngredients = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(float $quantity): static
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function isIsAllergen(): ?bool
    {
        return $this->isAllergen;
    }

    public function setIsAllergen(bool $isAllergen): static
    {
        $this->isAllergen = $isAllergen;

        return $this;
    }

    /**
     * @return Collection<int, DishIngredient>
     */
    public function getDishIngredients(): Collection
    {
        return $this->dishIngredients;
    }

    public function addDishIngredient(DishIngredient $dishIngredient): static
    {
        if (!$this->dishIngredients->contains($dishIngredient)) {
            $this->dishIngredients->add($dishIngredient);
            $dishIngredient->setIngredient($this);
        }

        return $this;
    }

    public function removeDishIngredient(DishIngredient $dishIngredient): static
    {
        if ($this->dishIngredients->removeElement($dishIngredient)) {
            // set the owning side to null (unless already changed)
            if ($dishIngredient->getIngredient() === $this) {
                $dishIngredient->setIngredient(null);
            }
        }

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(string $unit): static
    {
        $this->unit = $unit;

        return $this;
    }
}
