<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Serializer\SerializerInterface;
use App\Service\UtilsService;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTManager;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class EntityUpdaterService
{
    private $entityManager;
    private $serializer;
    private $passwordHasher;
    private $logger;
    private $imageProcessor;
    private $validator;
    private $transaction;
    private $userTokenGenerator;

    public function __construct(EntityManagerInterface $entityManager, SerializerInterface $serializer, UserPasswordHasherInterface $passwordHasher, LoggerInterface $logger, ImageProcessorService $imageProcessor, ValidatorInterface $validator, TransactionService $transaction, UserTokenGeneratorService $userTokenGenerator)
    {
        $this->entityManager = $entityManager;
        $this->serializer = $serializer;
        $this->passwordHasher = $passwordHasher;
        $this->logger = $logger;
        $this->imageProcessor = $imageProcessor;
        $this->validator = $validator;
        $this->transaction = $transaction;
        $this->userTokenGenerator = $userTokenGenerator;
    }

    public function updateEntity(Request $request, string $entityClassName, array $serializationGroups, array $deserializationGroups, array $serializationContext = [], int $id, bool $processImage = false): JsonResponse
    {
        try {

            // Je récupère l'id de l'entité à modifier passé en paramètre de la requête
            $idUpdatedEntity = $id;

            // On démarre une transaction juste avant de tenter de manipuler mes données
            $this->transaction->beginTransaction();

            // Je retrouve l'entité associée à cette id
            $entityToUpdate = $this->entityManager->getRepository($entityClassName)->find($idUpdatedEntity);

            // Si elle n'existe pas j'envoie une erreur
            if (!$entityToUpdate) {
                throw new RuntimeException(sprintf('L\'entité avec l\'id %d n\'existe pas.', $idUpdatedEntity), UtilsService::HTTP_NOT_FOUND);
            }

            // Si je suis dans la requête du traîtement exclusif à l'image
            if ($processImage) {

                // Je récupère le fichier image envoyé dans la requête
                $imageFile = $request->files->get('image');

                // Je me sers de mon service pour traiter l'image
                $this->imageProcessor->updateImage($entityToUpdate, $imageFile);
            } else {

                // On utilise le groupe de deserialization pour construire et hydrater notre entité
                $updatedEntity = $this->serializer->deserialize($request->getContent(), $entityClassName, 'json', ['groups' => $deserializationGroups] + $serializationContext);

                // Si malgré la maj de l'entité, certains champs supposés être unique n'ont pas changé et ont été renvoyés avec leur ancienne valeur
                if ($updatedEntity->getUsername() == $entityToUpdate->getUsername()) {

                    // On stocke cette ancienne valeur
                    $originalUsernameValue = $updatedEntity->getUsername();

                    // Et on met temporairement une valeur farfelue pour éviter les problèmes d'unicité dans la validation
                    $updatedEntity->setUsername('Valeur égale. Aucun changement requis');

                    $isUpdatedUsername = false;
                } else {

                    // Le Username va changer, il va falloir vérifier si un nouveau token est nécessaire
                    $isUpdatedUsername = true;
                }

                // Je recherche les erreurs liées aux contraintes de validation de mes colonnes
                $errors = $this->validator->validate($updatedEntity, null, ['update']);

                // Si il y a au moins une erreur
                if (count($errors) > 0) {
                    $errorMessages = [];

                    // Pour chacune d'entres elles
                    foreach ($errors as $error) {

                        // Je stocke le message d'erreur lié dans mon tableau d'erreurs
                        $errorMessages[] = $error->getMessage();
                    }

                    // Je renvoie au client ce tableau et le code d'erreur approprié
                    throw new RuntimeException(json_encode(['errors' => $errorMessages]), UtilsService::HTTP_BAD_REQUEST);
                }

                // Obtient la classe de l'entité
                $entityClass = new \ReflectionClass($entityToUpdate);

                // Obtient toutes les propriétés de la classe
                $properties = $entityClass->getProperties();

                // Tableau contenant les propriétés à ne pas traiter
                $excludeProperties = ['imageName', 'id', 'userRoles'];

                // Itère sur chaque propriété
                foreach ($properties as $property) {

                    // Permet de récupérer les valeurs de propriété private avec getValue sans utiliser les setters et getters
                    $property->setAccessible(true);

                    // Obtient le nom de la propriété
                    $propertyName = $property->getName();

                    // Obtient les valeurs actuelles des propriétés
                    $currentValue = $property->getValue($entityToUpdate);
                    $updatedValue = $property->getValue($updatedEntity);

                    // Compare les valeurs actuelles avec les nouvelles valeurs
                    if ($currentValue !== $updatedValue) {

                        // Si les password sont différents
                        if ($propertyName === 'password') {
                            if ($updatedValue !== "") {

                                // On le hash avant de le set
                                $hashedPassword = $this->passwordHasher->hashPassword($entityToUpdate, $updatedValue);
                                $property->setValue($entityToUpdate, $hashedPassword);
                            }


                            // On ne touche pas au nom de l'image, de l'id et des userRoles ici. Le traitement se fait dans le service ou controller approprié
                        } else if (!in_array($propertyName, $excludeProperties)) {

                            // Si on était dans le cas vu plus haut de champ unique non modifié
                            if ($propertyName === 'username' && $updatedValue === 'Valeur égale. Aucun changement requis') {

                                // On réattribue l'ancienne valeur qu'on avait stocké
                                $property->setValue($entityToUpdate, $originalUsernameValue);
                            } else {

                                // Met à jour la valeur de la propriété
                                $property->setValue($entityToUpdate, $updatedValue);
                            }
                        }
                    }
                }
            }

            // Persiste les modifications de l'entité avec la base de données
            $this->entityManager->persist($entityToUpdate);
            $this->entityManager->flush();

            // Aucune erreur n'a été levée jusqu'ici, je valide la transaction
            $this->transaction->commitTransaction();

            // Ajout des données spécifiques à l'entité User à la réponse
            $responseData['token'] = $isUpdatedUsername ?
                $this->userTokenGenerator->generateUserToken($entityToUpdate)
                : null;

            $responseData['message'] = 'Entité mise à jour avec succès';
            $responseData[strtolower(basename($entityClassName))] = UtilsService::serializeEntity($entityToUpdate, $serializationGroups, $this->serializer);

            return new JsonResponse($responseData, UtilsService::HTTP_OK);
        } catch (\RuntimeException $e) {

            return UtilsService::handleException($e->getMessage(), $e->getCode());
        } catch (Exception $e) {

            if ($this->transaction->isTransactionStarted()) {
                $this->transaction->rollbackTransaction();
            }

            return $this->transaction->handleException($e);
        }
    }
}
