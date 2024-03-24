<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Ingredient;
use App\Service\EntityCreatorService;
use App\Service\EntityDeleterService;
use App\Service\EntityUpdaterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

class IngredientController
{
    private $request;
    private $entityUpdater;
    private $entityCreator;
    private $entityDeleter;
    private $logger;


    public function __construct(
        Request $request,
        EntityUpdaterService $entityUpdater,
        EntityCreatorService $entityCreator,
        EntityDeleterService $entityDeleter,
        LoggerInterface $logger
    ) {
        $this->request = $request;
        $this->entityUpdater = $entityUpdater;
        $this->entityCreator = $entityCreator;
        $this->entityDeleter = $entityDeleter;
        $this->logger = $logger;
    }

    // Met à jour les informations d'un Ingredient
    #[Route('/api/ingredients/{id}', name: 'update_ingredient', methods: ['PUT'])]
    public function updateIngredient($id): JsonResponse
    {

        // Utilisation de mon service pour mettre à jour mon Ingredient
        return $this->entityUpdater->updateEntity(
            $this->request,
            Ingredient::class,
            ['ingredient:read'],
            ['ingredient:write'],
            $id,
            [],
            'IngredientUpdate'
        );
    }

    // Crée un nouveau Ingredient avec les informations passées en paramètre
    #[Route('/api/ingredients', name: 'register_ingredient', methods: ['POST'])]
    public function registerIngredient(): JsonResponse
    {

        // Utilisation de mon service pour créer mon Ingredient
        return $this->entityCreator->createEntity(
            $this->request,
            Ingredient::class,
            ['ingredient:read'],
            ['ingredient:write'],
            [],
            'IngredientRegister'
        );
    }

    #[Route('api/ingredients/{id}', name: 'register_ingredient_image', methods: ['POST'])]
    public function registerIngredientImage($id, Request $request): JsonResponse
    {

        // Utilisation de mon service pour créer mon Ingredient
        return $this->entityUpdater->updateEntity($request, Ingredient::class, [], [], [], $id);
    }

    // Supprime un ou plusieurs Ingredient dans les ingredientnames ont été passés en paramètre
    #[Route('/api/ingredients', name: 'delete_ingredient', methods: ['DELETE'])]
    public function deleteIngredient(): JsonResponse
    {

        // Utilisation de mon service pour supprimer un ou plusieurs Ingredients
        return $this->entityDeleter->deleteEntity($this->request, Ingredient::class);
    }
}
