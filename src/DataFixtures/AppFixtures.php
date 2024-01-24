<?php
// src\DataFixtures\AppFixtures.php

namespace App\DataFixtures;

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
    }
}
