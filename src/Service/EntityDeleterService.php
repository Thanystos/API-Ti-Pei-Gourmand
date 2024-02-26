<?php

namespace App\Service;

use App\Entity\Role;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Component\HttpFoundation\Request;
use App\Service\UtilsService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class EntityDeleterService
{
    private $entityManager;
    private $logger;
    private $queryService;
    private $params;
    private $imageProcessor;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, QueryService $queryService, ParameterBagInterface $params, ImageProcessorService $imageProcessor)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->queryService = $queryService;
        $this->params = $params;
        $this->imageProcessor = $imageProcessor;


        // Activer les points de sauvegarde pour les transactions
        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
    }

    public function deleteEntity(Request $request, string $entityClassName): JsonResponse
    {
        try {

            $this->entityManager->beginTransaction();

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
                ]
            ];

            // Je récupère les bonnes propriété en fonction de l'entité manipulée
            $properties = $entityProperties[$entityClassName];

            // Je crée un tableau contenant les usernames des User à supprimer
            $entitiesToDelete = $data[$properties['collection']];

            // Pour chaque username on va essayer de trouver une correspondance dans les User
            foreach ($entitiesToDelete as $entity) {

                // Je me sers de la méthode permettant de vérifier si le username appartient à un User
                $entity = $this->queryService->findOneByKey($entityClassName, $properties['field'], $entity);

                // Si je ne trouve aucune correspondance
                if (!$entity) {
                    throw new \RuntimeException(sprintf('L\'entité "%s" n\'existe pas.', $entity));
                }

                $deletedUserIds[] = $entity->getId();

                // Je traite la suppression de mon image
                $this->imageProcessor->deleteImage($entityClassName, $entity);

                // Sinon je supprime ce User
                $this->entityManager->remove($entity);
            }

            // Je mets à jour la bdd
            $this->entityManager->flush();

            // Aucune erreur n'a été levée jusqu'ici, je valide la transaction
            $this->entityManager->commit();

            // J'envoie la réponse au client
            return new JsonResponse([
                'message' => 'Utilisateurs supprimés avec succès',
                'user' => $deletedUserIds,
            ], UtilsService::HTTP_OK);
        } catch (\RuntimeException $e) {

            return UtilsService::handleException($e->getMessage(), UtilsService::HTTP_NOT_FOUND, $this->entityManager);
        } catch (\Exception $e) {

            return UtilsService::handleException($e->getMessage(), UtilsService::HTTP_INTERNAL_SERVER_ERROR, $this->entityManager);
        } catch (UniqueConstraintViolationException $e) {

            return UtilsService::handleException($e->getMessage(), UtilsService::HTTP_BAD_REQUEST, $this->entityManager);
        } catch (ForeignKeyConstraintViolationException $e) {

            return UtilsService::handleException($e->getMessage(), UtilsService::HTTP_BAD_REQUEST, $this->entityManager);
        } catch (NotNullConstraintViolationException $e) {

            return UtilsService::handleException($e->getMessage(), UtilsService::HTTP_BAD_REQUEST, $this->entityManager);
        }
    }
}
