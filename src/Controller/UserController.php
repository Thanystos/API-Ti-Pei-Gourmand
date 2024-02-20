<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\EntityCreatorService;
use App\Service\EntityUpdaterService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController
{
    private $request;
    private $entityUpdater;
    private $entityCreator;
    private $logger;


    public function __construct(Request $request, EntityUpdaterService $entityUpdater, EntityCreatorService $entityCreator, LoggerInterface $logger)
    {
        $this->request = $request;
        $this->entityUpdater = $entityUpdater;
        $this->entityCreator = $entityCreator;
        $this->logger = $logger;
    }

    // Met à jour les informations d'un User
    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser($id): JsonResponse
    {

        // Utilisation de mon service pour mettre à jour mon User
        return $this->entityUpdater->updateEntity($this->request, User::class, ['user:read'], ['user:write'], [], true, $id);
    }

    // Crée un nouveau User avec les informations passées en paramètre
    #[Route('/api/users', name: 'register_user', methods: ['POST'])]
    public function registerUser(): JsonResponse
    {

        // Utilisation de mon service pour créer mon User
        return $this->entityCreator->createEntity($this->request, User::class, ['user:read'], ['user:write'], [], true);
    }

    // Supprime un ou plusieurs User dans les usernames ont été passés en paramètre
    #[Route('/api/users', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(Request $request, UserRepository $userRepository, EntityManagerInterface $entityManager)
    {
        try {
            // J'exploite les paramètres de la requête
            $data = json_decode($request->getContent(), true);

            // Si le paramètre est absent ou qu'il ne s'agit pas d'un tableau ou que ce dernier est vide
            if (!isset($data['usernames']) || !is_array($data['usernames']) || empty($data['usernames'])) {
                throw new \InvalidArgumentException('Le paramètre contenant les utilisateurs à supprimer semble poser problème.');
            }

            // Je crée un tableau contenant les usernames des User à supprimer
            $usernamesToDelete = $data['usernames'];

            // Pour chaque username on va essayer de trouver une correspondance dans les User
            foreach ($usernamesToDelete as $username) {

                // J'appelle le repository des User
                $userRepository = $entityManager->getRepository(User::class);

                // Je me sers de la méthode permettant vérifier si le username appartient à un User
                $user = $userRepository->findOneByUsername($username);

                // Si je ne trouve aucune correspondance
                if (!$user) {
                    throw new \RuntimeException(sprintf('L\'utilisateur "%s" n\'existe pas.', $username));
                }

                $deletedUserIds[] = $user->getId();

                // Sinon je supprime ce User
                $entityManager->remove($user);
            }

            // Je mets à jour la bdd
            $entityManager->flush();

            // J'envoie la réponse au client
            return new JsonResponse([
                'message' => 'Utilisateurs supprimés avec succès',
                'user' => $deletedUserIds,
            ], JsonResponse::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur interne s\'est produite.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
