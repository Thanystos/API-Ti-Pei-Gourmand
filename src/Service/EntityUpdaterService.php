<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\UtilsService;
use Psr\Log\LoggerInterface;

class EntityUpdaterService
{
    private $entityManager;
    private $serializer;
    private $passwordHasher;
    private $security;
    private $jwtManager;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher, Security $security, JWTManager $jwtManager, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->passwordHasher = $passwordHasher;
        $this->security = $security;
        $this->jwtManager = $jwtManager;
        $this->logger = $logger;


        // Activer les points de sauvegarde pour les transactions
        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
    }

    public function updateEntity(Request $request, string $entityClassName, array $serializationGroups, array $deserializationGroups, array $serializationContext = [], bool $isUserEntity = false, $id): JsonResponse
    {
        try {

            $this->entityManager->beginTransaction();
            $this->logger->info('je suis un user ? : ' . $isUserEntity);

            // Je récupère l'id de l'entité à modifier passé en paramètre de la requête
            $idUpdatedEntity = $id;

            // Je retrouve l'entité associée à cette id
            $entityToUpdate = $this->entityManager->getRepository($entityClassName)->find($idUpdatedEntity);

            // Si elle n'existe pas j'envoie une erreur
            if (!$entityToUpdate) {
                throw new \RuntimeException(sprintf('L\'entité avec l\'id %d n\'existe pas.', $idUpdatedEntity));
            }

            // On utilise le groupe de deserialization pour construire et hydrater notre entité
            $updatedEntity = $this->serializer->deserialize($request->getContent(), $entityClassName, 'json', ['groups' => $deserializationGroups] + $serializationContext);

            // Obtient la classe de l'entité
            $entityClass = new \ReflectionClass($entityToUpdate);

            // Obtient toutes les propriétés de la classe
            $properties = $entityClass->getProperties();

            // Itère sur chaque propriété
            foreach ($properties as $property) {
                // Obtient le nom de la propriété
                $propertyName = $property->getName();

                // Construit les noms des méthodes getter et setter correspondantes
                $getterMethod = 'get' . ucfirst($propertyName);
                $setterMethod = 'set' . ucfirst($propertyName);

                // Vérifie si les méthodes getter et setter existent dans la classe
                if ($entityClass->hasMethod($getterMethod) && $entityClass->hasMethod($setterMethod)) {
                    // Obtient les valeurs actuelles des propriétés
                    $currentValue = $entityToUpdate->{$getterMethod}();
                    $updatedValue = $updatedEntity->{$getterMethod}();

                    // Compare les valeurs actuelles avec les nouvelles valeurs
                    if ($currentValue !== $updatedValue) {
                        // Si les password sont différents
                        if ($propertyName === 'password') {

                            // Et que celui de la modification n'est pas vide
                            if ($updatedValue !== "") {

                                // On le hash avant de le set
                                $hashedPassword = $this->passwordHasher->hashPassword($entityToUpdate, $updatedValue);
                                $entityToUpdate->{$setterMethod}($hashedPassword);
                            }
                        } else {
                            if ($propertyName !== 'userImage') {
                                // Met à jour la valeur de la propriété
                                $entityToUpdate->{$setterMethod}($updatedValue);
                            }
                        }
                    }
                }
            }

            // Persiste les modifications de l'entité avec la base de données
            $this->entityManager->persist($entityToUpdate);
            $this->entityManager->flush();

            // Aucune erreur n'a été levée jusqu'ici, je valide la transaction
            $this->entityManager->commit();

            if ($isUserEntity) {

                $this->logger->info('jentre dans la gestion des images');

                // Récupération de l'ID de l'image utilisateur
                $userImageId = $entityToUpdate->getUserImage()->getId();

                // Vérification si l'utilisateur connecté est celui qui est en train d'être modifié
                $loggedInUser = $this->security->getUser();
                $isConnected = ($loggedInUser && $loggedInUser->getUserIdentifier() === $entityToUpdate->getUsername());

                // Génération d'un nouveau token d'authentification si nécessaire
                $newToken = $isConnected ? $this->jwtManager->create($loggedInUser) : null;

                // Ajout des données spécifiques à l'entité User à la réponse
                $responseData['token'] = $newToken;
                $responseData['userImageId'] = $userImageId;
            }

            $responseData['message'] = 'Entité mise à jour avec succès';
            $responseData[strtolower(basename($entityClassName))] = UtilsService::serializeEntity($entityToUpdate, $serializationGroups, $this->serializer);

            return new JsonResponse($responseData, JsonResponse::HTTP_OK);
        } catch (\RuntimeException $e) {

            // Une erreur a été levée, j'annule la transaction
            $this->entityManager->rollback();

            // Je renvoie une réponse d'erreur au client
            return new JsonResponse(['error' => $e->getMessage()], UtilsService::HTTP_NOT_FOUND);
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