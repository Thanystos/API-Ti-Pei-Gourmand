<?php

namespace App\DataFixtures;

use App\Entity\Image;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class ImageFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        $image1 = new Image();
        $image1->setImageName("Image_1")
            ->setUser($this->getReference('thanystos'));

        $manager->persist($image1);

        $image2 = new Image();
        $image2->setImageName("Image_2")
            ->setUser($this->getReference('deykyana'));

        $manager->persist($image2);

        $image3 = new Image();
        $image3->setImageName("Image_3")
            ->setUser($this->getReference('mathieu'));

        $manager->persist($image3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
        ];
    }
}
