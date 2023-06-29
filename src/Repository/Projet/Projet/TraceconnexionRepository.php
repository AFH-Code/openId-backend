<?php

namespace App\Repository\Projet\Projet;

use App\Entity\Projet\Projet\Traceconnexion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Traceconnexion|null find($id, $lockMode = null, $lockVersion = null)
 * @method Traceconnexion|null findOneBy(array $criteria, array $orderBy = null)
 * @method Traceconnexion[]    findAll()
 * @method Traceconnexion[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TraceconnexionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Traceconnexion::class);
    }

    public function myFindOneFor($idprojet, $authcode)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.projet','p')
            ->addSelect('p')
            ->where('p.id = :idprojet')
            ->andWhere('t.authcode LIKE :authcode')
            ->setParameter('idprojet', $idprojet)
            ->setParameter('authcode', '%'.$authcode.'%')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function myFindByUser($userid, $limit)
    {
        return $this->createQueryBuilder('t')
            ->leftJoin('t.projet','p')
            ->leftJoin('t.user','u')
            ->addSelect('p')
            ->addSelect('u')
            ->where('u.id = :userid')
            ->setParameter('userid', $userid)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    // /**
    //  * @return Traceconnexion[] Returns an array of Traceconnexion objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Traceconnexion
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
