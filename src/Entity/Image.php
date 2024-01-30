<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Post;
use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
#[Vich\Uploadable]
#[ApiResource(
    operations: [
        new Post(
            name: 'register_image',
            uriTemplate: '/images/{id}',
            controller: 'App\Controller\ImageController::registerImage'
        ),
        new Delete(
            name: 'remove_image',
            uriTemplate: '/images/{id}',
            controller: 'App\Controller\ImageController::removeImage'
        )
    ]
)]

class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[
        assert\File(
            maxSize: "5M",
            mimeTypes: ["image/png", "image/jpeg", "image/gif"],
            mimeTypesMessage: "Veuillez télécharger une image valide (JPG, PNG, GIF)"
        )
    ]
    #[Vich\UploadableField(mapping: 'user_thumbnail', fileNameProperty: 'imageName')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['image:write'])]
    private ?string $imageName = null;

    #[ORM\OneToOne(mappedBy: 'userImage')]
    private ?User $user = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageName(?string $imageName): static
    {
        $this->imageName = $imageName;

        return $this;
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        // unset the owning side of the relation if necessary
        if ($user === null && $this->user !== null) {
            $this->user->setUserImage(null);
        }

        // set the owning side of the relation if necessary
        if ($user !== null && $user->getUserImage() !== $this) {
            $user->setUserImage($this);
        }

        $this->user = $user;

        return $this;
    }
}
