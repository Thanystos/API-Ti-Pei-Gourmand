<?php

namespace App\Service;

use App\Entity\User;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImageProcessorService
{
    private $params;
    private $logger;

    public function __construct(ParameterBagInterface $params, LoggerInterface $logger)
    {
        $this->params = $params;
        $this->logger = $logger;
    }

    // Permet la mise à jour de l'image et du fichier associé
    public function updateImage($entityToUpdate, $imageFile)
    {

        // Si le fichier de mon image est présent
        if (isset($imageFile)) {

            if ($imageFile instanceof UploadedFile) {

                // Je récupère le répertoire où sont stockeées mes images
                $imagesDirectory = $this->params->get('images_directory');

                // J'en déduis le chemin de l'image par défaut
                $defaultImagePath = $imagesDirectory . '/default_user_image.png';

                // J'en déduis le chemin de l'image utilisée par les datafixtures (à ne pas supprimer)
                $dataFixturesImagePath = $imagesDirectory . '/profil.jpg';

                // Je récupère l'ancien path de mon image
                $oldPath = $imagesDirectory . '/' . $entityToUpdate->getImageName();

                // Si l'ancien path de mon image n'est pas celui de l'image par défaut ou vide
                if ($oldPath !== $defaultImagePath && $oldPath !== $dataFixturesImagePath && $oldPath !== ($imagesDirectory . '/')) {

                    // Si le fichier existe
                    if (file_exists($oldPath)) {

                        // Je supprime cet ancien fichier
                        unlink($oldPath);
                    }
                }

                // Je crée un nom d'image pour ma nouvelle image
                $newImageName = uniqid() . '.' . $imageFile->getClientOriginalName();

                // Je l'attribue à mon user
                $entityToUpdate->setImageName($newImageName);

                // Je déplace mon image dans ce fichier
                $imageFile->move($imagesDirectory, $newImageName);
            } else {
                throw new RuntimeException('Le fichier envoyé n\'est pas une instance de UploadedFile.', UtilsService::HTTP_BAD_REQUEST);
            }
        } else {
            throw new RuntimeException('Aucun fichier image fourni.', UtilsService::HTTP_NOT_FOUND);
        }
    }

    // Permet la suppression de l'image et du fichier associé
    public function deleteImage($entityClassName, $entity)
    {

        // Si l'image à supprimer est associée à un User
        if ($entityClassName == User::class) {

            // Je vais chercher le bon dossier où trouver les images des User
            $imagePath = $this->params->get('images_directory') . '/' . $entity->getImageName();

            // Si on trouve son fichier image
            if (file_exists($imagePath)) {

                // Et que ce n'est pas celle par défaut
                if ($entity->getImageName() !== 'default_user_image.png' && $entity->getImageName() !== 'profil.jpg') {

                    // On le supprime
                    unlink($imagePath);
                }
            } else {
                throw new RuntimeException(sprintf('Aucune image trouvée pour "%s".', $entity->getUsername()), UtilsService::HTTP_NOT_FOUND);
            }
        }
    }
}
