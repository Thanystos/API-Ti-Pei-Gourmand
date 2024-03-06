<?php

namespace App\DataFixtures;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{

    private $userPasswordHasher;

    public function __construct(UserPasswordHasherInterface $userPasswordHasher)
    {
        $this->userPasswordHasher = $userPasswordHasher;
    }

    public function load(ObjectManager $manager)
    {

        $hireDate1 = new DateTime('2023-01-19T00:00:00');
        $endDate1 = new DateTime('2025-01-19T00:00:00');

        $hireDate2 = new DateTime('2020-01-19T00:00:00');
        $endDate2 = new DateTime('2028-01-19T00:00:00');

        $hireDate3 = new DateTime('2015-01-19T00:00:00');
        $endDate3 = new DateTime('2035-01-19T00:00:00');

        $user1 = new User();
        $user1->setUsername("Thanystos")
            ->setPassword($this->userPasswordHasher->hashPassword($user1, "Devilplop0"))
            ->setRealName("GUICHARD")
            ->setPhoneNumber("0771219079")
            ->setEmail("guichardanthony@live.fr")
            ->setHireDate($hireDate1)
            ->setEndDate($endDate1)
            ->setEmploymentStatus('CDD')
            ->setSocialSecurityNumber('0123456789876')
            ->setComments('Bon élément')
            ->setImageName('profil.jpg');

        $manager->persist($user1);
        $this->addReference('thanystos', $user1);

        $user2 = new User();
        $user2->setUsername("Deykyana")
            ->setPassword($this->userPasswordHasher->hashPassword($user2, "Devilplop_0"))
            ->setRealName("GUICHARD")
            ->setPhoneNumber("0123456789")
            ->setEmail("guichardchristopher@live.fr")
            ->setHireDate($hireDate2)
            ->setEndDate($endDate2)
            ->setEmploymentStatus('CDI')
            ->setSocialSecurityNumber('1857496254845')
            ->setComments('Mauvais élément')
            ->setImageName('profil.jpg');

        $manager->persist($user2);
        $this->addReference('deykyana', $user2);

        $user3 = new User();
        $user3->setUsername("Mathieu")
            ->setPassword($this->userPasswordHasher->hashPassword($user3, "Devilplop"))
            ->setRealName("JULIANS")
            ->setPhoneNumber("0846751387")
            ->setEmail("juliansmathieu@live.fr")
            ->setHireDate($hireDate3)
            ->setEndDate($endDate3)
            ->setEmploymentStatus('saisonnier')
            ->setSocialSecurityNumber('5719752347814')
            ->setComments('Toujours absent')
            ->setImageName('profil.jpg');

        $manager->persist($user3);
        $this->addReference('mathieu', $user3);

        $manager->flush();
    }
}
