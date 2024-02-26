<?php

namespace App\Service;

use App\Entity\User;
use Exception;
use Psr\Log\LoggerInterface;
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

                // Je récupère l'ancien path de mon image
                $oldPath = $imagesDirectory . '/' . $entityToUpdate->getImageName();

                // Si l'ancien path de mon image n'est pas celui de l'image par défaut ou vide
                if ($oldPath !== $defaultImagePath && $oldPath !== ($imagesDirectory . '/')) {

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
                throw new Exception('Aucun fichier image fourni');
            }
        }
    }

    // Permet la suppression de l'image et du fichier associé
    public function deleteImage($entityClassName, $entity)
    {
        if ($entityClassName == User::class) {
            $imagePath = $this->params->get('images_directory') . '/' . $entity->getImageName();
            $this->logger->info('imagePath = ' . $imagePath);

            if (file_exists($imagePath) && ($entity->getImageName() !== 'default_user_image.png')) {
                unlink($imagePath);
            }
        }
    }
}
