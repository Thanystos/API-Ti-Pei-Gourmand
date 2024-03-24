<?php

namespace App\Service;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UtilsService;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Exception;

class AssociativeEntityCreatorService
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

    public function createAssociativeEntity(
        Request $request,
        string $firstEntityClassName,
        string $secondEntityClassName,
        string $associativeEntityName
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

            $firstEntitySetter = 'set' . basename($firstEntityClassName);
            $secondEntitySetter = 'set' . basename($secondEntityClassName);

            $secondEntityGetter = 'get' . basename($secondEntityClassName);

            $associativeEntities = [];
            $associativeEntities = [];

            // On démarre une transaction juste avant de tenter de manipuler mes données
            $this->transaction->beginTransaction();

            // On associe les 2 entités grâce à notre entité d'association
            foreach ($foundEntities['secondEntities'] as $secondEntity) {
                $associativeEntity = new $associativeEntityName();
                $associativeEntity->$firstEntitySetter($foundEntities['firstEntity']);
                $associativeEntity->$secondEntitySetter($secondEntity);

                // Je signale à Doctrine de prendre en compte mon entité d'association
                $this->entityManager->persist($associativeEntity);

                $associativeEntities[] = $associativeEntity;
            }

            // Synchronise les modifications de l'entité avec la bdd
            $this->entityManager->flush();

            // Aucune erreur n'a été levée jusqu'ici, je valide la transaction
            $this->transaction->commitTransaction();

            // L'association a beau avoir été flush, l'entité associée n'est pas mise à jour. Il faut la refresh
            $this->entityManager->refresh($foundEntities['firstEntity']);

            // Ajout des données spécifiques à l'entité User à la réponse
            $responseData['token'] = (($firstEntityClassName === User::class
                && $secondEntityClassName === Role::class)
                && ($foundEntities['isLastMethod']))
                ? $this->userTokenGenerator->generateUserToken($foundEntities['firstEntity'])
                : null;

            foreach ($associativeEntities as $associativeEntity) {
                $associativeEntityDataAdded = [
                    '@id' => '/api/' . $firstEntityName . '_' . $secondEntityName . 's/' . $associativeEntity->getId(),
                    '@type' => strtolower(basename($associativeEntityName)),
                    'id' => $associativeEntity->getId(),
                    $secondEntityName => [
                        '@id' =>
                        '/api/' . $secondEntityName . '/s/' . $associativeEntity->$secondEntityGetter()->getId(),
                        '@type' => $secondEntityName,
                        'id' => $associativeEntity->$secondEntityGetter()->getId(),
                        'name' => $associativeEntity->$secondEntityGetter()->getName(),
                    ],
                ];
                $associativeEntitiesDataAdded[] = $associativeEntityDataAdded;
            }

            $responseData['message'] = 'Association(s) ajoutée(s) avec succès';
            $responseData[strtolower(basename($firstEntityClassName))] = $foundEntities['firstEntityId'];
            $responseData['compositionsAdded'] = $associativeEntitiesDataAdded;

            return new JsonResponse($responseData, JsonResponse::HTTP_CREATED);
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
