<?php

namespace App\Entity;

use App\Repository\DishIngredientRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DishIngredientRepository::class)]
class DishIngredient
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'dishIngredients')]
    private ?Dish $dish = null;

    #[ORM\ManyToOne(inversedBy: 'dishIngredients')]
    private ?Ingredient $ingredient = null;

    #[ORM\Column(nullable: true)]
    private ?float $quantityNeeded = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDish(): ?Dish
    {
        return $this->dish;
    }

    public function setDish(?Dish $dish): static
    {
        $this->dish = $dish;

        return $this;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): static
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getQuantityNeeded(): ?float
    {
        return $this->quantityNeeded;
    }

    public function setQuantityNeeded(?float $quantityNeeded): static
    {
        $this->quantityNeeded = $quantityNeeded;

        return $this;
    }
}
