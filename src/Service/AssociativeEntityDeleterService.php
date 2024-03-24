<?php

namespace App\Service;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UtilsService;
use Exception;
use Psr\Log\LoggerInterface;
use RuntimeException;

class AssociativeEntityDeleterService
{
    private $entityManager;
    private $logger;
    private $transaction;
    private $entitiesFinder;
    private $userTokenGenerator;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        TransactionService $transaction,
        EntitiesFinderService $entitiesFinder,
        UserTokenGeneratorService $userTokenGenerator
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->transaction = $transaction;
        $this->entitiesFinder = $entitiesFinder;
        $this->userTokenGenerator = $userTokenGenerator;
    }

    public function deleteAssociativeEntity(
        Request $request,
        string $firstEntityClassName,
        string $secondEntityClassName,
        string $associativeEntityClassName
    ): JsonResponse {
        try {
            // Utilisation de mon service pour vérifier l'existance et trouver mes 2 entités
            $foundEntities = $this->entitiesFinder->findEntities(
                $request,
                $firstEntityClassName,
                $secondEntityClassName
            );

            $firstEntityName = strtolower(basename($firstEntityClassName));
            $secondEntityName = strtolower(basename($secondEntityClassName));

            $associativeEntitiesDataRemoved = [];

            // On démarre une transaction juste avant de tenter de manipuler mes données
            $this->transaction->beginTransaction();

            foreach ($foundEntities['secondEntities'] as $secondEntity) {
                $associativeEntityRepository = $this->entityManager->getRepository($associativeEntityClassName);

                if (!$associativeEntityRepository) {
                    throw new RuntimeException(
                        'Le repository de l\'entité n\'a pas été trouvé.',
                        UtilsService::HTTP_NOT_FOUND
                    );
                }

                $associativeEntity = $associativeEntityRepository->findOneBy(
                    [$firstEntityName => $foundEntities['firstEntity'],
                    $secondEntityName => $secondEntity]
                );

                if (!$associativeEntity) {
                    throw new RuntimeException(
                        'L\'entité d\'association pour une des entités n\'a pas été trouvée',
                        UtilsService::HTTP_NOT_FOUND
                    );
                }

                $associativeEntitiesDataRemoved[] = $associativeEntity->getId();
                $this->entityManager->remove($associativeEntity);
            }

            $this->entityManager->flush();

            $this->transaction->commitTransaction();

            // L'association a beau avoir été flush, l'entité associée n'est pas mise à jour. Il faut la refresh.
            $this->entityManager->refresh($foundEntities['firstEntity']);

            // Ajout des données spécifiques à l'entité User à la réponse
            $responseData['token'] = (
                $firstEntityClassName === User::class
                && $secondEntityClassName === Role::class
            )
            ? $this->userTokenGenerator->generateUserToken($foundEntities['firstEntity'])
            : null;

            $responseData['message'] = 'Association(s) supprimée(s) avec succès';
            $responseData[$firstEntityName] = $foundEntities['firstEntityId'];
            $responseData['compositionsRemoved'] = $associativeEntitiesDataRemoved;

            return new JsonResponse($responseData, JsonResponse::HTTP_OK);
        } catch (RuntimeException $e) {
            return UtilsService::handleException($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            if ($this->transaction->isTransactionStarted()) {
                $this->transaction->rollbackTransaction();
            }

            return $this->transaction->handleException($e);
        }
    }
}
