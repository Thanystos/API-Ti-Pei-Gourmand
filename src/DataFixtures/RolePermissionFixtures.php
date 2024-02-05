<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\RolePermission;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class RolePermissionFixtures extends Fixture implements DependentFixtureInterface
{

    public function load(ObjectManager $manager)
    {
        // Associations entre les rôles et les permissions
        $rolePermission1 = new RolePermission();
        $rolePermission1->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('user_read'));

        $manager->persist($rolePermission1);

        $rolePermission2 = new RolePermission();
        $rolePermission2->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('user_write'));

        $manager->persist($rolePermission2);

        $rolePermission3 = new RolePermission();
        $rolePermission3->setRole($this->getReference('role_cuisinier'))
            ->setPermission($this->getReference('user_write'));

        $manager->persist($rolePermission3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            RoleFixtures::class,
            PermissionFixtures::class,
        ];
    }
}
