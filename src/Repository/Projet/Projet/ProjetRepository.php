<?php

namespace App\Repository\Projet\Projet;

use App\Entity\Projet\Projet\Projet;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Common\Collections\Criteria;

/**
 * @method Projet|null find($id, $lockMode = null, $lockVersion = null)
 * @method Projet|null findOneBy(array $criteria, array $orderBy = null)
 * @method Projet[]    findAll()
 * @method Projet[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProjetRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Projet::class);
    }

    public function myfindOneBy($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.projetUniq = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    public function myFindByUser($userid, $page, $numberPerPage)
    {
        if ($page < 1) {
            throw new \InvalidArgumentException('Page inexistant');
        }
        $firstResult = ($page -1) * $numberPerPage;
        $query = $this->createQueryBuilder('p')
            ->leftJoin('p.user','u')
            ->addSelect('u')

            ->where('u.id = :userid')
            ->setParameter('userid', $userid)
            ->orderBy('p.date', 'DESC');

        $criteria = Criteria::create()
                    ->setFirstResult($firstResult)
                    ->setMaxResults($numberPerPage);
        $query->addCriteria($criteria);

        $doctrinePaginator = new DoctrinePaginator($query);
        $paginator = new Paginator($doctrinePaginator);

        $total = (int) $this->findProjetUser($userid);

        return array('total'=>$total, 'page'=> (int) $page, 'totalItems'=>count($paginator), 'data'=>$paginator);
    }

    public function findProjetUser($userid)
    {
       $query = $this->_em->createQuery('SELECT COUNT(p.id) FROM App\Entity\Projet\Projet\Projet p, App\Entity\Users\User\User u WHERE p.user = u AND u.id = :userId');
       $query->setParameter('userId',$userid);
       return $query->getSingleScalarResult();
    }
}
