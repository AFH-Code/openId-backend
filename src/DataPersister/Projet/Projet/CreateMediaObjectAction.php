<?php
namespace App\DataPersister\Projet\Projet;
use App\Repository\Projet\Projet\ProjetRepository;
use App\Entity\Projet\Projet\Projet;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;

final class CreateMediaObjectAction extends AbstractController
{
    const OPERATION_NAME = 'post_logo';

    const OPERATION_PATH = '/user/{id}/projets/logo';

    private $repository;
    private $_entityManager;

    public function __construct(ProjetRepository $repository, EntityManagerInterface $entityManager)
    {
        $this->repository = $repository;
        $this->_entityManager = $entityManager;
    }

    /**
     * @param Request $request
     *
     * @return Projet
     */
    public function __invoke(Request $request): Projet
    {
        $uploadedFile = $request->files->get('file');
        if(!$uploadedFile){
            throw new BadRequestHttpException('"file" is required');
        }
        $id = $request->attributes->get('id');
        $id = $request->request->get('nom');
        $organization = $this->repository->find($id);
        
        //$organization->logoFile = $uploadedFile;
        $organization->updateLogo($uploadedFile);

        $this->_entityManager->flush();

        return $organization;
    }
}