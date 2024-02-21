<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;

class UtilsService
{
    // Constantes contenant les codes HTTP des réponses
    public const HTTP_OK = 200;
    public const HTTP_BAD_REQUEST = 400;
    public const HTTP_NOT_FOUND = 404;
    public const HTTP_INTERNAL_SERVER_ERROR = 500;

    // Permet de la sérialisation de mes entités en vue de former la réponse à renvoyer au client
    public static function serializeEntity($entity, array $serializationGroups, $serializer): array
    {
        $entityData = $serializer->serialize($entity, 'json', ['groups' => $serializationGroups]);
        return ['@id' => '/api/' . strtolower((new \ReflectionClass($entity))->getShortName()) . '/' . $entity->getId(), '@type' => (new \ReflectionClass($entity))->getShortName()] + json_decode($entityData, true);
    }

    // Permet de centraliser la gestion des exceptions et de renvoyer le message d'erreur associé
    public static function handleException(string $errorMessage, int $errorCode, $entityManager): JsonResponse
    {
        // Annulation de la transaction en cas d'erreur
        $entityManager->rollback();

        // Renvoi de la réponse d'erreur au client
        return new JsonResponse(['error' => $errorMessage], $errorCode);
    }
}
