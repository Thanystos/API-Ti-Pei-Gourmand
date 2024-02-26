<?php

namespace App\Service;

use App\Service\UtilsService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityCreatorService
{

    private $entityManager;
    private $serializer;
    private $validator;
    private $passwordHasher;
    private $logger;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;

        // Activer les points de sauvegarde pour les transactions
        $this->entityManager->getConnection()->setNestTransactionsWithSavepoints(true);
    }

    public function createEntity(Request $request, $entityClassName, array $serializationGroups = [], array $deserializationGroups = [], array $serializationContext = [], bool $isUserEntity = false)
    {
        try {

            $this->entityManager->beginTransaction();

            // On utilise le groupe de deserialization pour construire et hydrater notre entité
            $createdEntity = $this->serializer->deserialize($request->getContent(), $entityClassName, 'json', ['groups' => $deserializationGroups] + $serializationContext);

            // Je recherche les erreurs liées aux contraintes de validation de mes colonnes
            $errors = $this->validator->validate($createdEntity, [], []);

            // Si il y a au moins une erreur
            if (count($errors) > 0) {
                $errorMessages = [];

                // Pour chacune d'entres elles
                foreach ($errors as $error) {

                    // Je stocke le message d'erreur lié dans mon tableau d'erreurs
                    $errorMessages[] = $error->getMessage();
                }

                // Je renvoie au client ce tableau et le code d'erreur approprié
                return new JsonResponse(['errors' => $errorMessages], UtilsService::HTTP_BAD_REQUEST);
            }

            if ($isUserEntity) {

                // Hashage du mot de passe si fourni
                $hashedPassword = $this->passwordHasher->hashPassword($createdEntity, $createdEntity->getPassword());
                $createdEntity->setPassword($hashedPassword);
            }

            $data = json_decode($request->getContent(), true);

            if (isset($data['hasImage']) && !$data['hasImage']) {
                $createdEntity->setImageName('default_user_image.png');
            }

            // Je signale à Doctrine de prendre en compte mon entité User
            $this->entityManager->persist($createdEntity);

            // Synchronise les modifications de l'entité avec la bdd
            $this->entityManager->flush();

            // Aucune erreur n'a été levée jusqu'ici, je valide la transaction
            $this->entityManager->commit();

            $responseData = [
                'message' => 'Entité créée avec succès',
                strtolower(basename($entityClassName)) => UtilsService::serializeEntity($createdEntity, $serializationGroups, $this->serializer),
            ];

            return new JsonResponse($responseData, UtilsService::HTTP_OK);
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
