<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\DishIngredient;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;

class DishIngredientFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {

        // Associations entre les ingrédients et les plats
        $dishIngredient1 = new DishIngredient();
        $dishIngredient1->setDish($this->getReference('pizza'))
            ->setIngredient($this->getReference('tomate'))
            ->setQuantityNeeded(150);

        $manager->persist($dishIngredient1);


        $dishIngredient2 = new DishIngredient();
        $dishIngredient2->setDish($this->getReference('pizza'))
            ->setIngredient($this->getReference('fromage'))
            ->setQuantityNeeded(100);

        $manager->persist($dishIngredient2);


        $dishIngredient3 = new DishIngredient();
        $dishIngredient3->setDish($this->getReference('spaghetti'))
            ->setIngredient($this->getReference('tomate'))
            ->setQuantityNeeded(200);

        $manager->persist($dishIngredient3);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [
            IngredientFixtures::class,
            DishFixtures::class,
        ];
    }
}
