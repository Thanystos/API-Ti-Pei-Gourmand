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

        $hireDate4 = null;
        $endDate4 = null;

        $hireDate5 = new DateTime('2012-01-19T00:00:00');
        $endDate5 = new DateTime('2037-01-19T00:00:00');

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

        $user4 = new User();
        $user4->setUsername("Jean-François")
            ->setPassword($this->userPasswordHasher->hashPassword($user4, "Devilplop"))
            ->setRealName("JOLY")
            ->setPhoneNumber("2548756214")
            ->setEmail("jf@live.fr")
            ->setHireDate($hireDate4)
            ->setEndDate($endDate4)
            ->setEmploymentStatus('CDI')
            ->setSocialSecurityNumber('5792461348513')
            ->setComments('Très motivé')
            ->setImageName('profil.jpg');

        $manager->persist($user4);
        $this->addReference('jeanfrancois', $user4);

        $user5 = new User();
        $user5->setUsername("Pec")
            ->setPassword($this->userPasswordHasher->hashPassword($user5, "Devilplop"))
            ->setRealName("ADRIEN")
            ->setPhoneNumber("4758612485")
            ->setEmail("pec@live.fr")
            ->setHireDate($hireDate5)
            ->setEndDate($endDate5)
            ->setEmploymentStatus('CDD')
            ->setSocialSecurityNumber('6943187624865')
            ->setComments('Fais le mariole')
            ->setImageName('profil.jpg');

        $manager->persist($user5);
        $this->addReference('pec', $user5);

        $manager->flush();
    }
}
