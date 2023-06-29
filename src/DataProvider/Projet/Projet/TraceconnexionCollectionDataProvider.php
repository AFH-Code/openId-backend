<?php
namespace App\DataProvider\Projet\Projet;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Projet\Projet\Traceconnexion;
use App\Repository\Projet\Projet\TraceconnexionRepository;
use Symfony\Component\HttpFoundation\RequestStack;
use App\Service\Users\User\UserService;

final class TraceconnexionCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $_traceconnexionRepository;
    private $_requestStack;
    private $_userService;

    public function __construct(
        TraceconnexionRepository $traceconnexionRepository,
        RequestStack $requestStack,
        UserService $userService
    ){
        $this->_traceconnexionRepository = $traceconnexionRepository;
        $this->_requestStack = $requestStack;
        $this->_userService = $userService;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Traceconnexion::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        // Retrieve the blog post collection from somewhere

        $request = $this->_requestStack->getCurrentRequest();
        $position = $request->attributes->get('position');
        if($position == 'dashboard')
        {
            $liste_traceconnexion = $this->_traceconnexionRepository->myFindByUser($this->_userService->getConnectUser()->getId(), 10);
        }else{
            $liste_traceconnexion = $this->_traceconnexionRepository->findAll();
        }

        $username = $this->_userService->getConnectUser()->getFirstName();

        foreach($liste_traceconnexion as $traceconnexion){
            $traceconnexion->getProjet()->setNom($position .'-'.$username);
            yield $traceconnexion;
        }
    }
}