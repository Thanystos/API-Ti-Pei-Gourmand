<?php

namespace App\DataFixtures;

use App\Entity\Dish;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class DishFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {

        // Création de plats
        $dish1 = new Dish();
        $dish1->setTitle('Pizza Margarita');
        $dish1->setDescription('Pizza classique avec du fromage et des tomates');
        $dish1->setCategory('Pizza');
        $dish1->setPicture("Image1");
        $dish1->setPrice(10.99);
        $dish1->setIsAvailable(true);
        $dish1->setIsAllergen(false);

        $manager->persist($dish1);
        $this->addReference('pizza', $dish1);

        $dish2 = new Dish();
        $dish2->setTitle('Spaghetti Bolognaise');
        $dish2->setDescription('Spaghetti avec des tomates et de la sauce');
        $dish2->setCategory('Pâtes');
        $dish2->setPicture("Image2");
        $dish2->setPrice(12.99);
        $dish2->setIsAvailable(true);
        $dish2->setIsAllergen(false);

        $manager->persist($dish2);
        $this->addReference('spaghetti', $dish2);

        $manager->flush();
    }
}
