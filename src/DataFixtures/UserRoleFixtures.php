<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\UserRole;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class UserRoleFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        // Associations entre les utilisateurs et les rôles
        $userRole1 = new UserRole();
        $userRole1->setUser($this->getReference('thanystos'))
            ->setRole($this->getReference('role_admin'));

        $manager->persist($userRole1);

        $userRole2 = new UserRole();
        $userRole2->setUser($this->getReference('deykyana'))
            ->setRole($this->getReference('role_admin'));

        $manager->persist($userRole2);

        $userRole3 = new UserRole();
        $userRole3->setUser($this->getReference('mathieu'))
            ->setRole($this->getReference('role_cuisinier'));

        $manager->persist($userRole3);

        $userRole4 = new UserRole();
        $userRole4->setUser($this->getReference('jeanfrancois'))
            ->setRole($this->getReference('role_admin'));

        $manager->persist($userRole4);

        $userRole5 = new UserRole();
        $userRole5->setUser($this->getReference('pec'))
            ->setRole($this->getReference('role_serveur'));

        $manager->persist($userRole5);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            UserFixtures::class,
            RoleFixtures::class,
        ];
    }
}
