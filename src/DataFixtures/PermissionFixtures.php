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
        $userReadPermission = new Permission();
        $userReadPermission->setName('Accès à la liste des employés')
            ->setScope('Permissions relatives à la gestion des employés');

        $manager->persist($userReadPermission);
        $this->addReference('user_read', $userReadPermission);

        $userWritePermission = new Permission();
        $userWritePermission->setName('Inscription de nouveaux employés')
            ->setScope('Permissions relatives à la gestion des employés');

        $manager->persist($userWritePermission);
        $this->addReference('user_write', $userWritePermission);

        $userUpdatePermission = new Permission();
        $userUpdatePermission->setName('Mise à jour des informations des employés')
            ->setScope('Permissions relatives à la gestion des employés');

        $manager->persist($userUpdatePermission);
        $this->addReference('user_update', $userUpdatePermission);

        $userDeletePermission = new Permission();
        $userDeletePermission->setName('Suppression des employés')
            ->setScope('Permissions relatives à la gestion des employés');

        $manager->persist($userDeletePermission);
        $this->addReference('user_delete', $userDeletePermission);

        # -------------------------------------------------------- #

        // Création de 2 permissions
        $permissionReadPermission = new Permission();
        $permissionReadPermission->setName('Accès à la liste des rôles et permissions associées')
            ->setScope('Permissions relatives à la gestion des rôles');

        $manager->persist($permissionReadPermission);
        $this->addReference('permission_read', $permissionReadPermission);

        // Création de 2 permissions
        $permissionUpdatePermission = new Permission();
        $permissionUpdatePermission->setName('Mise à jour des permissions pour les rôles associés')
            ->setScope('Permissions relatives à la gestion des rôles');

        $manager->persist($permissionUpdatePermission);
        $this->addReference('permission_update', $permissionUpdatePermission);

        # -------------------------------------------------------- #

        $ingredientReadPermission = new Permission();
        $ingredientReadPermission->setName('Accès à la liste des ingrédients')
            ->setScope('Permissions relatives à la gestion des ingrédients');

        $manager->persist($ingredientReadPermission);
        $this->addReference('ingredient_read', $ingredientReadPermission);

        $ingredientWritePermission = new Permission();
        $ingredientWritePermission->setName('Inscription de nouveaux ingrédients')
            ->setScope('Permissions relatives à la gestion des ingrédients');

        $manager->persist($ingredientWritePermission);
        $this->addReference('ingredient_write', $ingredientWritePermission);

        // Création de 2 permissions
        $IngredientUpdatePermission = new Permission();
        $IngredientUpdatePermission->setName('Mise à jour des ingrédients')
            ->setScope('Permissions relatives à la gestion des ingrédients');

        $manager->persist($IngredientUpdatePermission);
        $this->addReference('ingredient_update', $IngredientUpdatePermission);

        // Création de 2 permissions
        $IngredientDeletePermission = new Permission();
        $IngredientDeletePermission->setName('Suppression des ingrédients')
            ->setScope('Permissions relatives à la gestion des ingrédients');

        $manager->persist($IngredientDeletePermission);
        $this->addReference('ingredient_delete', $IngredientDeletePermission);

        $manager->flush();
    }
}
