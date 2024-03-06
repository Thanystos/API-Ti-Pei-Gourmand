<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Psr\Log\LoggerInterface;
use RuntimeException;

class EntitiesFinderService
{
    private $entityManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }

    public function findEntities(Request $request, string $firstEntityClassName, string $secondEntityClassName): array
    {
        // On extrait les informations de la requête
        $requestData = json_decode($request->getContent(), true);

        // On vérifie que le repository de la première entité passée existe réellement
        $firstEntityRepository = $this->entityManager->getRepository($firstEntityClassName);

        // On léve une exception si ce n'est pas le cas
        if (!$firstEntityRepository) {
            throw new RuntimeException('Le repository de la première entité n\'a pas été trouvé.', UtilsService::HTTP_NOT_FOUND);
        }

        // On trouve l'entité grâce à son id passé dans le body de la request
        $firstEntity = $firstEntityRepository->find($requestData['firstEntityId']);

        // Si on ne la trouve pas on lève une exception
        if (!$firstEntity) {
            throw new RuntimeException('L\'entité de la première entité avec l\'ID spécifié n\'a pas été trouvée.', UtilsService::HTTP_NOT_FOUND);
        }

        // On récupère son id
        $firstEntityId = $firstEntity->getId();

        // On vérifie que le repository de la seconde entité passée existe réellement
        $secondEntityRepository = $this->entityManager->getRepository($secondEntityClassName);

        if (!$secondEntityRepository) {
            throw new RuntimeException('Le repository de la première entité n\'a pas été trouvé.', UtilsService::HTTP_NOT_FOUND);
        }

        // On trouve les entités grâce aux ids passés dans le body de la request
        $secondEntities = $secondEntityRepository->findBy(['id' => $requestData['secondEntityIds']]);

        // Si on trouve moins d'entités qu'il n'y a d'id dans le body de la request c'est que certaines n'ont pas été trouvées
        if (count($secondEntities) !== count($requestData['secondEntityIds'])) {
            throw new RuntimeException('Certaines des deuxièmes entités n\'ont pas été trouvées', UtilsService::HTTP_NOT_FOUND);
        }

        return [
            'firstEntity' => $firstEntity,
            'secondEntities' => $secondEntities,
            'firstEntityId' => $firstEntityId,
            'isLastMethod' => $requestData['isLastMethod'] ?? null,
        ];
    }
}
