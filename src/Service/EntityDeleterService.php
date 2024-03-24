<?php

namespace App\Service;

use App\Entity\Ingredient;
use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UtilsService;
use Exception;
use Psr\Log\LoggerInterface;

class EntityDeleterService
{
    private $entityManager;
    private $logger;
    private $queryService;
    private $imageProcessor;
    private $transaction;

    public function __construct(
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        QueryService $queryService,
        ImageProcessorService $imageProcessor,
        TransactionService $transaction
    ) {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->queryService = $queryService;
        $this->imageProcessor = $imageProcessor;
        $this->transaction = $transaction;
    }

    public function deleteEntity(Request $request, string $entityClassName): JsonResponse
    {
        try {
            // J'exploite les paramètres de la requête
            $data = json_decode($request->getContent(), true);

            $entityProperties = [
                User::class => [
                    'field' => 'username',
                    'collection' => 'usernames',
                ],
                Role::class => [
                    'field' => 'name',
                    'collection' => 'names',
                ],
                Ingredient::class => [
                    'field' => 'title',
                    'collection' => 'titles',
                ]
            ];

            // Je récupère les bonnes propriétés en fonction de l'entité manipulée
            $properties = $entityProperties[$entityClassName];

            // Je crée un tableau contenant les usernames des User à supprimer
            $entitiesToDelete = $data[$properties['collection']];
            $this->logger->info('entitytodelete : ', [$entitiesToDelete]);

            // On démarre une transaction juste avant de tenter de manipuler mes données
            $this->transaction->beginTransaction();

            // Pour chaque username on va essayer de trouver une correspondance dans les User
            foreach ($entitiesToDelete as $entity) {
                // Je me sers de la méthode permettant de vérifier si le username appartient à un User
                $entity = $this->queryService->findOneByKey($entityClassName, $properties['field'], $entity);

                // Si je ne trouve aucune correspondance
                if (!$entity) {
                    throw new \RuntimeException(
                        sprintf(
                            'L\'entité "%s" n\'existe pas.',
                            $entity
                        ),
                        UtilsService::HTTP_NOT_FOUND
                    );
                }

                // Je stocke dans mon tableau les ids de toutes les entités
                $deletedEntityIds[] = $entity->getId();

                // Seules les entités possédant une image peuvent supprimer cette dernière
                if ($entityClassName === User::class) {
                    // Je traite la suppression de mon image
                    $this->imageProcessor->deleteImage($entityClassName, $entity);
                }


                // Sinon je supprime ce User
                $this->entityManager->remove($entity);
            }

            // Je mets à jour la bdd
            $this->entityManager->flush();

            // Aucune erreur n'a été levée jusqu'ici, je valide la transaction
            $this->transaction->commitTransaction();

            // J'envoie la réponse au client
            return new JsonResponse([
                'message' => 'Entités supprimées avec succès',
                strtolower(basename($entityClassName)) => $deletedEntityIds,
            ], UtilsService::HTTP_OK);
        } catch (\RuntimeException $e) {
            $this->logger->info('problème de runtime');
            $this->logger->info('message : ' . $e->getMessage());
            $this->logger->info('code : ' . $e->getMessage());

            return UtilsService::handleException($e->getMessage(), $e->getCode());
        } catch (Exception $e) {
            $this->logger->info('problème de runtime');
            $this->logger->info('message : ' . $e->getMessage());
            $this->logger->info('code : ' . $e->getMessage());

            if ($this->transaction->isTransactionStarted()) {
                $this->transaction->rollbackTransaction();
            }

            return $this->transaction->handleException($e);
        }
    }
}
