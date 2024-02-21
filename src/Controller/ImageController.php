<?php

namespace App\Controller;

use App\Entity\Image;
use App\Service\EntityCreatorService;
use App\Service\EntityDeleterService;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ImageController
{

    private $logger;
    private $entityCreator;
    private $entityDeleter;

    public function __construct(LoggerInterface $logger, EntityCreatorService $entityCreator, EntityDeleterService $entityDeleter)
    {
        $this->logger = $logger;
        $this->entityCreator = $entityCreator;
        $this->entityDeleter = $entityDeleter;
    }

    // Crée une nouvelle Image avec l'id du User qui y sera associé passé en paramètre
    #[Route('/api/images/{id}', name: 'register_image', methods: ['POST'])]
    public function registerImage(Request $request, $id): JsonResponse
    {
        return $this->entityCreator->createEntity($request, Image::class, [], [], [], false, $id);
    }

    // Supprime une Image identifiée par son id passé en paramètre
    #[Route('/api/images/{id}', name: 'delete_image', methods: ['DELETE'])]
    public function deleteImage(Request $request, $id): JsonResponse
    {
        return $this->entityDeleter->deleteEntity($request, Image::class, $id);
    }
}
