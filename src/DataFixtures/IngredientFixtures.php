<?php

namespace App\DataFixtures;

use App\Entity\Ingredient;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class IngredientFixtures extends Fixture
{

    public function load(ObjectManager $manager)
    {

        // Création d'ingrédients
        $ingredient1 = new Ingredient();
        $ingredient1->setTitle('Tomate')
            ->setQuantity(200)
            ->setIsAllergen(false);

        $manager->persist($ingredient1);
        $this->addReference('tomate', $ingredient1);

        $ingredient2 = new Ingredient();
        $ingredient2->setTitle('Fromage')
            ->setQuantity(150)
            ->setIsAllergen(false);

        $this->addReference('fromage', $ingredient2);
        $manager->persist($ingredient2);

        $manager->flush();
    }
}
