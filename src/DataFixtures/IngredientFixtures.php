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
            ->setCategory('Fruits')
            ->setQuantity(200)
            ->setUnit('Kg')
            ->setIsAllergen(false);

        $manager->persist($ingredient1);
        $this->addReference('tomate', $ingredient1);

        $ingredient2 = new Ingredient();
        $ingredient2->setTitle('Fromage')
            ->setCategory('Produits laitiers')
            ->setQuantity(150)
            ->setUnit('Kg')
            ->setIsAllergen(false);

        $manager->persist($ingredient2);
        $this->addReference('fromage', $ingredient2);

        $ingredient3 = new Ingredient();
        $ingredient3->setTitle('Piment')
            ->setCategory('Condiment')
            ->setQuantity(20)
            ->setUnit('Kg')
            ->setIsAllergen(false);

        $manager->persist($ingredient3);
        $this->addReference('piment', $ingredient3);

        $ingredient4 = new Ingredient();
        $ingredient4->setTitle('Steak de boeuf')
            ->setCategory('Viande')
            ->setQuantity(80)
            ->setUnit('Kg')
            ->setIsAllergen(false);

        $manager->persist($ingredient4);
        $this->addReference('steakdeboeuf', $ingredient4);

        $ingredient5 = new Ingredient();
        $ingredient5->setTitle('Lait')
            ->setCategory('Produits laitiers')
            ->setQuantity(200)
            ->setUnit('Kg')
            ->setIsAllergen(false);

        $manager->persist($ingredient5);
        $this->addReference('lait', $ingredient5);


        $manager->flush();
    }
}
