<?php

namespace App\Repository\Users\User;

use App\Entity\Users\User\Imgprofil;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Imgprofil|null find($id, $lockMode = null, $lockVersion = null)
 * @method Imgprofil|null findOneBy(array $criteria, array $orderBy = null)
 * @method Imgprofil[]    findAll()
 * @method Imgprofil[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ImgprofilRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Imgprofil::class);
    }

    // /**
    //  * @return Imgprofil[] Returns an array of Imgprofil objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Imgprofil
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
