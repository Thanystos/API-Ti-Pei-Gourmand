<?php

namespace App\Repository;

use App\Entity\DishIngredient;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DishIngredient>
 *
 * @method DishIngredient|null find($id, $lockMode = null, $lockVersion = null)
 * @method DishIngredient|null findOneBy(array $criteria, array $orderBy = null)
 * @method DishIngredient[]    findAll()
 * @method DishIngredient[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DishIngredientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DishIngredient::class);
    }

//    /**
//     * @return DishIngredient[] Returns an array of DishIngredient objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('d.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?DishIngredient
//    {
//        return $this->createQueryBuilder('d')
//            ->andWhere('d.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
