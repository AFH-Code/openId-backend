<?php

namespace App\Repository\Projet\Projet;

use App\Entity\Projet\Projet\Accesstoken;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Accesstoken|null find($id, $lockMode = null, $lockVersion = null)
 * @method Accesstoken|null findOneBy(array $criteria, array $orderBy = null)
 * @method Accesstoken[]    findAll()
 * @method Accesstoken[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AccesstokenRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Accesstoken::class);
    }

    // /**
    //  * @return Accesstoken[] Returns an array of Accesstoken objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Accesstoken
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
