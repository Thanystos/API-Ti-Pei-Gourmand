<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;

class QueryService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    // Requête générique permettant de trouver une entité en fonction du nom de la classe et de la colonne associée
    public function findOneByKey(string $entityClassName, string $key, $value)
    {
        $repository = $this->entityManager->getRepository($entityClassName);
        $qb = $repository->createQueryBuilder('e');

        switch ($key) {
            case 'username':
                $qb->andWhere('e.username = :value')->setParameter('value', $value);
                break;
            case 'name':
                $qb->andWhere('e.name = :value')->setParameter('value', $value);
                break;
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
