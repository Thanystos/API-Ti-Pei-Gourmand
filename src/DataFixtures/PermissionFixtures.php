<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Permission;

class PermissionFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {

        // Création de 2 permissions
        $readFunction = new Permission();
        $readFunction->setName('USER_READ');

        $manager->persist($readFunction);
        $this->addReference('user_read', $readFunction);

        $writeFunction = new Permission();
        $writeFunction->setName('USER_WRITE');

        $manager->persist($writeFunction);
        $this->addReference('user_write', $writeFunction);

        $manager->flush();
    }
}
