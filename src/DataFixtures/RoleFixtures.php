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
        $adminRole->setName('Administrateur');

        $manager->persist($adminRole);
        $this->addReference('role_admin', $adminRole);

        $cuisinierRole = new Role();
        $cuisinierRole->setName('Cuisinier');

        $manager->persist($cuisinierRole);
        $this->addReference('role_cuisinier', $cuisinierRole);

        $serveurRole = new Role();
        $serveurRole->setName('Serveur');

        $manager->persist($serveurRole);
        $this->addReference('role_serveur', $serveurRole);

        $manager->flush();
    }
}
