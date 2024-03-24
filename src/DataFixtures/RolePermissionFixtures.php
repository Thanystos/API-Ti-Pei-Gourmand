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
        $rolePermission3->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('user_update'));

        $manager->persist($rolePermission3);

        $rolePermission4 = new RolePermission();
        $rolePermission4->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('user_delete'));

        $manager->persist($rolePermission4);

        $rolePermission5 = new RolePermission();
        $rolePermission5->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('permission_read'));

        $manager->persist($rolePermission5);

        $rolePermission6 = new RolePermission();
        $rolePermission6->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('permission_update'));

        $manager->persist($rolePermission6);





        $rolePermission7 = new RolePermission();
        $rolePermission7->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('ingredient_read'));

        $manager->persist($rolePermission7);

        $rolePermission8 = new RolePermission();
        $rolePermission8->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('ingredient_write'));

        $manager->persist($rolePermission8);

        $rolePermission9 = new RolePermission();
        $rolePermission9->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('ingredient_update'));

        $manager->persist($rolePermission9);

        $rolePermission10 = new RolePermission();
        $rolePermission10->setRole($this->getReference('role_admin'))
            ->setPermission($this->getReference('ingredient_delete'));

        $manager->persist($rolePermission10);

        $rolePermission11 = new RolePermission();
        $rolePermission11->setRole($this->getReference('role_cuisinier'))
            ->setPermission($this->getReference('user_read'));

        $manager->persist($rolePermission11);

        $rolePermission12 = new RolePermission();
        $rolePermission12->setRole($this->getReference('role_serveur'))
            ->setPermission($this->getReference('user_read'));

        $manager->persist($rolePermission12);

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
