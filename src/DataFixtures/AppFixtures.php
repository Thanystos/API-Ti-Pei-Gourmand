<?php
// src\DataFixtures\AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Dish;
use App\Entity\DishIngredient;
use App\Entity\Ingredient;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager): void
    {

        // DataFixtures pour les Users
        $user1 = new User();
        $user1->setUsername("Thanystos");
        $user1->setRoles(["ROLE_ADMIN"]);
        $user1->setPassword($this->userPasswordHasher->hashPassword($user1, "Devilplop0"));
        $user1->setRealName("GUICHARD");
        $user1->setPhoneNumber("0771219079");
        $user1->setEmail("guichardanthony@live.fr");

        $hireDate1 = new DateTime('2023-01-19T00:00:00');
        $user1->setHireDate($hireDate1);

        $manager->persist($user1);

        $user2 = new User();
        $user2->setUsername("Deykyana");
        $user2->setRoles(["ROLE_ADMIN"]);
        $user2->setPassword($this->userPasswordHasher->hashPassword($user2, "Devilplop0"));
        $user2->setRealName("GUICHARD");
        $user2->setPhoneNumber("0123456789");
        $user2->setEmail("guichardchristopher@live.fr");

        $hireDate2 = new DateTime('2021-01-19T00:00:00');
        $user2->setHireDate($hireDate2);
        $manager->persist($user2);

        $manager->flush();


        // DataFixtures pour les Plats, Ingrédients et la table de composition des 2

        // Création d'ingrédients
        $ingredient1 = new Ingredient();
        $ingredient1->setTitle('Tomate');
        $ingredient1->setQuantity(200);
        $ingredient1->setIsAllergen(false);
        $manager->persist($ingredient1);

        $ingredient2 = new Ingredient();
        $ingredient2->setTitle('Fromage');
        $ingredient2->setQuantity(150);
        $ingredient2->setIsAllergen(false);
        $manager->persist($ingredient2);

        // Création de plats
        $dish1 = new Dish();
        $dish1->setTitle('Pizza Margarita');
        $dish1->setDescription('Pizza classique avec du fromage et des tomates');
        $dish1->setCategory('Pizza');
        $dish1->setPicture("Image1");
        $dish1->setPrice(10.99);
        $dish1->setIsAvailable(true);
        $dish1->setIsAllergen(false);
        $dish1->addIngredient($ingredient1);
        $dish1->addIngredient($ingredient2);
        $manager->persist($dish1);

        $dish2 = new Dish();
        $dish2->setTitle('Spaghetti Bolognaise');
        $dish2->setDescription('Spaghetti avec des tomates et de la sauce');
        $dish2->setCategory('Pâtes');
        $dish2->setPicture("Image2");
        $dish2->setPrice(12.99);
        $dish2->setIsAvailable(true);
        $dish2->setIsAllergen(false);
        $dish2->addIngredient($ingredient1);
        $manager->persist($dish2);

        // Ajout de quantités nécessaires pour chaque ingrédient dans chaque plat
        $dishIngredient1 = new DishIngredient();
        $dishIngredient1->setDish($dish1);
        $dishIngredient1->setIngredient($ingredient1);
        $dishIngredient1->setQuantityNeeded(150);
        $manager->persist($dishIngredient1);

        $dishIngredient2 = new DishIngredient();
        $dishIngredient2->setDish($dish1);
        $dishIngredient2->setIngredient($ingredient2);
        $dishIngredient2->setQuantityNeeded(100);
        $manager->persist($dishIngredient2);

        $dishIngredient3 = new DishIngredient();
        $dishIngredient3->setDish($dish2);
        $dishIngredient3->setIngredient($ingredient1);
        $dishIngredient3->setQuantityNeeded(200);
        $manager->persist($dishIngredient3);

        // Enregistrement des données dans la base de données
        $manager->flush();
    }
}
