<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Component\HttpFoundation\JsonResponse;

class UtilsService
{
    // Constantes contenant les codes HTTP des réponses
    public const HTTP_OK = 200;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    // Permet la sérialisation de mes entités en vue de former la réponse à renvoyer au client
    public static function serializeEntity($entity, array $serializationGroups, $serializer, string $name): array
    {
        $entityData = $serializer->serialize($entity, 'json', ['groups' => $serializationGroups]);
        $decodedEntityData = json_decode($entityData, true);

        // Ajouter les clés "@id" et "@type" pour l'entité principale
        $decodedEntityData = [
            '@id' => '/api/' . strtolower((new \ReflectionClass($entity))->getShortName()) . 's/' . $entity->getId(),
            '@type' => (new \ReflectionClass($entity))->getShortName(),
        ] + $decodedEntityData;


        if ($name === User::class) {
            // Ajouter les clés "@id" et "@type" au début de chaque objet "UserRole" dans le tableau "userRoles"
            foreach ($decodedEntityData['userRoles'] as &$userRole) {
                $userRole = [
                    '@id' => '/api/user_roles/' . $userRole['id'],
                    '@type' => 'UserRole',
                ] + $userRole;
            }
        }

        return $decodedEntityData;
    }

    // Permet de centraliser la gestion des exceptions et de renvoyer le message d'erreur associé
    public static function handleException(
        $errorMessage,
        int $errorCode = JsonResponse::HTTP_INTERNAL_SERVER_ERROR
    ): JsonResponse {
        $errorData = is_array($errorMessage) ?
            ['errors' => $errorMessage]
            : ['error' => $errorMessage];

        // Renvoi de la réponse d'erreur au client
        return new JsonResponse($errorData, $errorCode);
    }
}
