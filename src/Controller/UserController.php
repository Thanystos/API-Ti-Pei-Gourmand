<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Service\EntityCreatorService;
use App\Service\EntityDeleterService;
use App\Service\EntityUpdaterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    private $request;
    private $entityUpdater;
    private $entityCreator;
    private $entityDeleter;
    private $logger;


    public function __construct(Request $request, EntityUpdaterService $entityUpdater, EntityCreatorService $entityCreator, EntityDeleterService $entityDeleter, LoggerInterface $logger)
    {
        $this->request = $request;
        $this->entityUpdater = $entityUpdater;
        $this->entityCreator = $entityCreator;
        $this->entityDeleter = $entityDeleter;
        $this->logger = $logger;
    }

    // Met à jour les informations d'un User
    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser($id): JsonResponse
    {

        // Utilisation de mon service pour mettre à jour mon User
        return $this->entityUpdater->updateEntity($this->request, User::class, ['user:read'], ['user:write'], [], $id);
    }

    // Crée un nouveau User avec les informations passées en paramètre
    #[Route('/api/users', name: 'register_user', methods: ['POST'])]
    public function registerUser(): JsonResponse
    {

        // Utilisation de mon service pour créer mon User
        return $this->entityCreator->createEntity($this->request, User::class, ['user:read'], ['user:write'], [], true);
    }

    #[Route('api/users/{id}', name: 'register_user_image', methods: ['POST'])]
    public function registerUserImage($id, Request $request): JsonResponse
    {

        // Utilisation de mon service pour créer mon User
        return $this->entityUpdater->updateEntity($request, User::class, [], [], [], $id, true);
    }

    // Supprime un ou plusieurs User dans les usernames ont été passés en paramètre
    #[Route('/api/users', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(): JsonResponse
    {

        // Utilisation de mon service pour supprimer un ou plusieurs Users
        return $this->entityDeleter->deleteEntity($this->request, User::class);
    }
}
