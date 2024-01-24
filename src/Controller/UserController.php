<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

//#[AsController] Permet l'autoconfiguration du controleur
class UserController
{
    private Security $security;
    private JWTTokenManagerInterface $jwtManager;
    private LoggerInterface $logger;
    public function __construct(Security $security, JWTTokenManagerInterface $jwtManager, LoggerInterface $logger)
    {
        $this->security = $security;
        $this->jwtManager = $jwtManager;
        $this->logger = $logger;
    }

    // Méthode qui peut traiter POST et PUT et qui se différencie en fonction de la présence de l'id dans les paramètres
    private function processUserRequest(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator, $idUpdatedUser = null): JsonResponse
    {

        // On extrait les informations de la requête
        $requestData = json_decode($request->getContent(), true);

        // Si l'id n'est pas présent dans les paramètres il s'agit du traitement d'une requête POST
        if (!$idUpdatedUser) {

            // Créez un nouvel utilisateur si $user n'est pas fourni (cas de la méthode POST)
            $user = new User();

            // Sinon c'est celui de PUT
        } else {

            // J'appelle le repository des User
            $userRepository = $entityManager->getRepository(User::class);

            // Je me sers de la méthode permettant de trouver un user d'id passé en param
            $user = $userRepository->find($idUpdatedUser);
        }

        // Si l'image a été définie côté client
        if (isset($requestData['image']) && $requestData['image'] !== "") {

            // Je change l'image du User par celle de la requête
            $user->setImage($requestData['image']);
        }

        // Si le password a été défini côté client
        if (isset($requestData['password']) && $requestData['password'] !== "") {

            // Je me sers du bundle pour hasher le password
            $hashedPassword = $passwordHasher->hashPassword($user, $requestData['password']);

            // Je change le realname du User par celui de la requête
            $user->setPassword($hashedPassword);
        }

        // Je convertis le String de date en "vraie" Date
        $datedHireDate = ($requestData['hireDate'] !== "")
            ? DateTime::createFromFormat('Y-m-d', $requestData['hireDate'])
            : null;

        // Utilisation du service de journalisation

        // Enregistrement d'un message dans les logs
        // $this->logger->info('Contenu de $requestData["hireDate"]: ' . $requestData['hireDate']);


        // Je change le username du User par celui de la requête
        $user->setUsername($requestData['username'])

            // Je change les roles du User par ceux de la requête
            ->setRoles($requestData['roles'])

            // Je change le realname du User par celui de la requête
            ->setRealname($requestData['realName'])

            // Je change le phoneNumber du User par celui de la requête
            ->setPhoneNumber($requestData['phoneNumber'])

            // Je change l'email du User par celui de la requête
            ->setEmail($requestData['email'])

            // Je change le hireDate du User par celui mise en forme Date de la requête
            ->setHireDate($datedHireDate);

        // Je recherche les erreurs liées aux contraintes de validation de mes colonnes
        $errors = $validator->validate($user);

        // Si il y a au moins une erreur
        if (count($errors) > 0) {
            $errorMessages = [];

            // Pour chacune d'entres elles
            foreach ($errors as $error) {

                // Je stocke le message d'erreur lié dans mon tableau d'erreurs
                $errorMessages[] = $error->getMessage();
            }

            // Je renvoie au client ce tableau et le code d'erreur approprié
            return new JsonResponse(['errors' => $errorMessages], JsonResponse::HTTP_BAD_REQUEST);
        }

        // J'enregistre mon nouveau User dans la bdd
        $entityManager->persist($user);

        // Je mets à jour la bdd
        $entityManager->flush();

        // Pour plus de lisibilité, je stocke le retour de mes getters dans des variables
        $id = $user->getId();
        $username = $user->getUsername();
        $realName = $user->getRealName();
        $image = $user->getImage();
        $phoneNumber = $user->getPhoneNumber();
        $email = $user->getEmail();
        $hireDate = $user->getHireDate();

        if ($idUpdatedUser) {
            $loggedInUser = $this->security->getUser();
            $isConnected = ($loggedInUser && $loggedInUser->getUserIdentifier() === $requestData['username']);

            // Vérification si l'utilisateur modifié est le même que celui connecté
            // Si le userIdentifier existe et qu'il est le même que celui de la requête
            if ($isConnected) {

                // Je créée un nouveau token d'identification
                $newToken = $this->jwtManager->create($loggedInUser);
            }
        }
        // Je crée la réponse qui sera envoyée au client
        $responseData = [
            'message' => 'Utilisateur mis à jour avec succès',
            'token' => $isConnected ? $newToken : null,
            'user' => [
                'id' => $id,
                'username' => $username,
                'realName' => $realName,
                'image' => $image,
                'phoneNumber' => $phoneNumber,
                'email' => $email,
                'hireDate' => $hireDate,
            ]
        ];

        // J'envoie la réponse au client
        return new JsonResponse($responseData, JsonResponse::HTTP_OK);
    }


    // Met à jour les informations d'un User
    #[Route('/api/users/{id}', name: 'update_user', methods: ['PUT'])]
    public function updateUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {

        // Je récupère l'id passé en paramètre de la requête
        $idUpdatedUser = $request->get('id');

        // Méthode pouvant traiter la requête POST ET PUT
        return $this->processUserRequest($request, $entityManager, $passwordHasher, $validator, $idUpdatedUser);
    }

    // Crée un nouveau User avec les informations passées en paramètre
    #[Route('/api/users', name: 'register_user', methods: ['POST'])]
    public function registerUser(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, ValidatorInterface $validator): JsonResponse
    {

        // Méthode pouvant traiter la requête POST ET PUT
        return $this->processUserRequest($request, $entityManager, $passwordHasher, $validator);
    }

    // Supprime un ou plusieurs User dans les usernames ont été passés en paramètre
    #[Route('/api/users', name: 'delete_user', methods: ['DELETE'])]
    public function deleteUser(Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository)
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

                // Sinon je supprime ce User
                $entityManager->remove($user);
            }

            // Je mets à jour la bdd
            $entityManager->flush();

            // J'envoie la réponse au client
            return new JsonResponse(['message' => 'Utilisateurs supprimés avec succès'], JsonResponse::HTTP_OK);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Une erreur interne s\'est produite.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
