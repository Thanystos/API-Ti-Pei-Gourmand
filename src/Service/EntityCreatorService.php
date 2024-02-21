<?php

namespace App\Service;

use App\Entity\Image;
use App\Entity\User;
use App\Service\UtilsService;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

    public function createEntity(Request $request, $entityClassName, array $serializationGroups = [], array $deserializationGroups = [], array $serializationContext = [], bool $isUserEntity = false, int $id = null)
    {
        try {

            $this->entityManager->beginTransaction();

            if ($entityClassName == Image::class) {

                // On récupère l'id de l'utilisateur qu'on a inscrit ou modifié
                $userId = $id;

                // Je récupère ce dernier gràce à son id
                $user = $this->entityManager->getRepository(User::class)->find($userId);

                if (!$user) {
                    throw new \Exception('Utilisateur non trouvé.');
                }

                // Je récupère le fichier image envoyé dans la requête
                $imageFile = $request->files->get('image');
                $this->logger->info('Contenu de $imageFile : ' . print_r($imageFile, true));

                // Je crée une nouvelle image pour ma table Image
                $image = new Image();

                // Si un fichier image a été envoyé
                if (isset($imageFile)) {
                    $this->logger->info('jentre dans le traitement de limage');
                    if ($imageFile instanceof UploadedFile) {

                        // J'associe le fichier à cette nouvelle entrée
                        $image->setImageFile($imageFile);
                    } else {
                        throw new \Exception('Aucun fichier image fourni.');
                    }

                    // J'associe le nom de fichier à cette nouvelle entrée
                    $image->setImageName($imageFile->getClientOriginalName());

                    // Si aucun fichier image n'a été envoyé
                } else {

                    // Je pointe l'image par défaut
                    $image->setImageName('%kernel.project_dir%/public/images/users/default_user_image.png');
                }

                // J'associe cette nouvelle image à mon User
                $image->setUser($user);

                // J'enregistre ma nouvelle Image dans la bdd
                $this->entityManager->persist($image);

                // Je mets à jour la bdd
                $this->entityManager->flush();

                // Aucune erreur n'a été levée jusqu'ici, je valide la transaction
                $this->entityManager->commit();

                // Je crée la réponse qui sera envoyée au client
                $responseData = [
                    'message' => 'Image créée avec succès',
                    'imageName' => $image->getImageName(),
                    'imageUser' => $image->getUser(),
                ];

                // J'envoie la réponse au client
                return new JsonResponse($responseData, JsonResponse::HTTP_OK);
            } else {
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
