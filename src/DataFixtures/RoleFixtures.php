<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Role;

class RoleFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {
        // Création de deux rôles
        $adminRole = new Role();
        $adminRole->setName('ROLE_ADMIN');

        $manager->persist($adminRole);
        $this->addReference('role_admin', $adminRole);

        $cuisinierRole = new Role();
        $cuisinierRole->setName('ROLE_CUISINIER');

        $manager->persist($cuisinierRole);
        $this->addReference('role_cuisinier', $cuisinierRole);

        $manager->flush();
    }
}
