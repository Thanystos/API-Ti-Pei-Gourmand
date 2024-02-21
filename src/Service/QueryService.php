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

    public function findOneByKey(string $entityClassName, string $key, $value)
    {
        $repository = $this->entityManager->getRepository($entityClassName);
        $qb = $repository->createQueryBuilder('e');

        // Construire dynamiquement la condition WHERE en fonction de la clé
        switch ($key) {
            case 'username':
                $qb->andWhere('e.username = :value')->setParameter('value', $value);
                break;
            case 'name':
                $qb->andWhere('e.name = :value')->setParameter('value', $value);
                break;
                // Ajouter d'autres cas selon les besoins
        }

        return $qb->getQuery()->getOneOrNullResult();
    }
}
