<?php
namespace App\DataPersister\Projet\Projet;

use App\Entity\Users\User\User;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\ResumableDataPersisterInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Servicetext\GeneralServicetext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Projet\Projet\Projet;

/**
 *
*/
class ProjetDataPersister implements ContextAwareDataPersisterInterface, ResumableDataPersisterInterface
{
    private $_entityManager;
    private $_service;
    private $helperService;

    public function __construct(
        EntityManagerInterface $entityManager,
        GeneralServicetext $service
    ) { 
        $this->_entityManager = $entityManager;
        $this->_service = $service;
    }

    /**
     * {@inheritdoc}
    */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Projet;
    }

    /**
     * @param Projet $data
    */
    public function persist($data, array $context = [])
    {
        return $result;
    }

    /**
     * {@inheritdoc}
    */
    public function remove($data, array $context = [])
    {
        $this->_entityManager->remove($data);
        $this->_entityManager->flush();
    }

    // Once called this data persister will resume to the next one
    public function resumable(array $context = []): bool 
    {
        return true;
    }
}
