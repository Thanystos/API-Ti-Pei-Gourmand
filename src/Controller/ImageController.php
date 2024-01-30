<?php

namespace App\Controller;

use App\Entity\Image;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

class ImageController
{

    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    // Crée une nouvelle Image avec l'id du User qui y sera associé passé en paramètre
    #[Route('/api/images/{id}', name: 'register_image', methods: ['POST'])]
    public function registerImage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {

            // On récupère l'id de l'utilisateur qu'on a inscrit ou modifié
            $userId = $request->get('id');

            // Je récupère ce dernier gràce à son id
            $user = $entityManager->getRepository(User::class)->find($userId);

            if (!$user) {
                throw new \Exception('Utilisateur non trouvé.');
            }

            // Je récupère le fichier image envoyé dans la requête
            $imageFile = $request->files->get('image');

            // Je crée une nouvelle image pour ma table Image
            $image = new Image();
            if ($imageFile instanceof UploadedFile) {

                // J'associe le fichier à cette nouvelle entrée
                $image->setImageFile($imageFile);
            } else {
                throw new \Exception('Aucun fichier image fourni.');
            }

            // J'associe le nom de fichier à cette nouvelle entrée
            $image->setImageName($imageFile->getClientOriginalName());

            // J'associe cette nouvelle image à mon User
            $image->setUser($user);

            // J'enregistre ma nouvelle Image dans la bdd
            $entityManager->persist($image);

            // Je mets à jour la bdd
            $entityManager->flush();

            // Je crée la réponse qui sera envoyée au client
            $responseData = [
                'message' => 'Image créée avec succès',
                'imageName' => $image->getImageName(),
                'imageUser' => $image->getUser(),
            ];

            // J'envoie la réponse au client
            return new JsonResponse($responseData, JsonResponse::HTTP_OK);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Supprime une Image identifiée par son id passé en paramètre
    #[Route('/api/images/{id}', name: 'remove_image', methods: ['DELETE'])]
    public function removeImage(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        try {
            // Je récupère l'id de l'image à supprimer passé en paramètre
            $imageId = $request->get('id');

            // Je récupère l'image associée
            $image = $entityManager->getRepository(Image::class)->find($imageId);

            // Si je ne trouve aucune concordance
            if (!$image) {

                // J'envoie la réponse d'erreur au client
                throw new JsonResponse(['error' => 'Image non trouvée.'], JsonResponse::HTTP_NOT_FOUND);
            }

            // Supprime l'image
            $entityManager->remove($image);

            // Je mets à jour la bdd
            $entityManager->flush();

            // J'envoie la réponse au client
            return new JsonResponse(['Image effacée avec succès'], JsonResponse::HTTP_OK);
        } catch (\Exception) {
            return new JsonResponse(['error' => 'Erreur lors de la suppression de l\'image.'], JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
