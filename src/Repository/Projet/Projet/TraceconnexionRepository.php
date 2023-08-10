<?php

namespace App\Repository\Projet\Projet;

use App\Entity\Projet\Projet\Traceconnexion;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Paginator;
use Doctrine\ORM\Tools\Pagination\Paginator as DoctrinePaginator;
use Doctrine\Common\Collections\Criteria;

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

    public function myFindByUser($userid, $page, $numberPerPage)
    {
        if ($page < 1) {
            throw new \InvalidArgumentException('Page inexistant');
        }
        $firstResult = ($page -1) * $numberPerPage;
        $query = $this->createQueryBuilder('t')
            ->leftJoin('t.projet','p')
            ->leftJoin('t.user','u')
            ->addSelect('p')
            ->addSelect('u')

            ->where('u.id = :userid')
            ->setParameter('userid', $userid)
            ->orderBy('t.date', 'DESC');

        $criteria = Criteria::create()
                    ->setFirstResult($firstResult)
                    ->setMaxResults($numberPerPage);
        $query->addCriteria($criteria);

        $doctrinePaginator = new DoctrinePaginator($query);
        $paginator = new Paginator($doctrinePaginator);

        $total = (int) $this->findCategorySearch($userid);

        return array('total'=>$total, 'page'=> (int) $page, 'totalItems'=>count($paginator), 'data'=>$paginator);
    }

    public function findCategorySearch($userid)
    {
       $query = $this->_em->createQuery('SELECT COUNT(t.id) FROM App\Entity\Projet\Projet\Traceconnexion t, App\Entity\Users\User\User u WHERE t.user = u AND u.id = :userId');
       $query->setParameter('userId',$userid);
       return $query->getSingleScalarResult();
    }

}
