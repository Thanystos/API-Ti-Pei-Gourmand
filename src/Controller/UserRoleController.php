<?php

namespace App\Controller;

use App\Entity\Role;
use App\Entity\User;
use App\Entity\UserRole;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class UserRoleController
{
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    // Crée de nouvelles associations entre un User et un ou plusieurs rôles
    #[Route('/api/user_roles', name: 'register_userRoles', methods: ['POST'])]
    public function registerUserRoles(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // On extrait les informations de la requête
        $requestData = json_decode($request->getContent(), true);

        // Récupération de l'utilisateur
        $user = $entityManager->find(User::class, $requestData['user']);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $userId = $user->getId();

        // Récupération des rôles
        $roles = $entityManager->getRepository(Role::class)->findBy(['id' => $requestData['roles']]);
        if (count($roles) !== count($requestData['roles'])) {
            return new JsonResponse(['error' => 'Certains rôles n\'ont pas été trouvés'], JsonResponse::HTTP_NOT_FOUND);
        }

        $userRolesDataAdded = [];

        // Création et association des UserRole
        foreach ($roles as $role) {
            $userRole = new UserRole();
            $userRole->setUser($user);
            $userRole->setRole($role);
            $entityManager->persist($userRole);
            $entityManager->flush();

            $userRoleData = [
                '@id' => '/api/user_roles/' . $userRole->getId(),
                '@type' => 'UserRole',
                'id' => $userRole->getId(),
                'role' => [
                    '@id' => '/api/roles/' . $role->getId(),
                    '@type' => 'Role',
                    'id' => $role->getId(),
                    'name' => $role->getName(),
                ],
            ];
            $userRolesDataAdded[] = $userRoleData;
        }

        return new JsonResponse([
            'message' => 'Association(s) User-Roles ajoutées avec succès',
            'user' => $userId,
            'compositionsAdded' => $userRolesDataAdded
        ], JsonResponse::HTTP_CREATED);
    }

    // Crée une nouvelle association entre un User et un ou plusieurs rôles
    #[Route('/api/user_roles', name: 'delete_userRoles', methods: ['DELETE'])]
    public function deleteUserRoles(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // On extrait les informations de la requête
        $requestData = json_decode($request->getContent(), true);

        // Récupération de l'utilisateur
        $user = $entityManager->find(User::class, $requestData['user']);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non trouvé'], JsonResponse::HTTP_NOT_FOUND);
        }

        $userId = $user->getId();

        // Récupération des rôles
        $roles = $entityManager->getRepository(Role::class)->findBy(['id' => $requestData['roles']]);
        if (count($roles) !== count($requestData['roles'])) {
            return new JsonResponse(['error' => 'Certains rôles n\'ont pas été trouvés'], JsonResponse::HTTP_NOT_FOUND);
        }

        $userRolesDataRemoved = [];

        // Suppression des UserRole associés à l'utilisateur et aux rôles spécifiés
        foreach ($roles as $role) {
            $userRole = $entityManager->getRepository(UserRole::class)->findOneBy(['user' => $user, 'role' => $role]);
            if ($userRole) {
                $userRolesDataRemoved[] = $userRole->getId();
                $entityManager->remove($userRole);
            }
        }

        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Association(s) User-Roles supprimées avec succès',
            'user' => $userId,
            'compositionsRemoved' => $userRolesDataRemoved,
        ], JsonResponse::HTTP_OK);
    }
}
