<?php

namespace App\Service;

use App\Entity\Image;
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

class EntityDeleterService
{
    private $entityManager;
    private $logger;
    private $queryService;

    public function __construct(EntityManagerInterface $entityManager, LoggerInterface $logger, QueryService $queryService)
    {
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->queryService = $queryService;


        // Activer les points de sauvegarde pour les transactions
        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
    }

    public function deleteEntity(Request $request, string $entityClassName, int $id = null): JsonResponse
    {
        try {

            $this->entityManager->beginTransaction();

            if ($entityClassName == Image::class) {
                // Je récupère l'id de l'image à supprimer passé en paramètre
                $imageId = $id;
                $this->logger->info('lid vaut : ' . $id);

                // Je récupère l'image associée
                $image = $this->entityManager->getRepository(Image::class)->find($imageId);
                $this->logger->info('id du user de limage avant suppression : ' . $image->getUser()->getId());

                // Si je ne trouve aucune concordance
                if (!$image) {
                    $this->logger->info('je suis entré dans le bug');

                    // J'envoie la réponse d'erreur au client
                    throw new JsonResponse(['error' => 'Image non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
                }

                // Je brise la relation de l'image avec son user
                $this->logger->info('je vais briser la relation');
                $image->setUser(null);

                $this->logger->info('la relation devrait être brisée');
                $this->logger->info('id du user de limage après suppression : ' . $image->getUser()->getId());

                // Supprime l'image
                $this->entityManager->remove($image);

                // Je mets à jour la bdd
                $this->entityManager->flush();

                // Aucune erreur n'a été levée jusqu'ici, je valide la transaction
                $this->entityManager->commit();

                // J'envoie la réponse au client
                return new JsonResponse(['Image effacée avec succès'], JsonResponse::HTTP_OK);
            } else {

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

                    // Je me sers de la méthode permettant vérifier si le username appartient à un User
                    $entity = $this->queryService->findOneByKey($entityClassName, $properties['field'], $entity);

                    // Si je ne trouve aucune correspondance
                    if (!$entity) {
                        throw new \RuntimeException(sprintf('L\'entité "%s" n\'existe pas.', $entity));
                    }

                    $deletedUserIds[] = $entity->getId();

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
            }
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
