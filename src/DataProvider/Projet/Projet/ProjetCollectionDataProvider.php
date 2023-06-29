<?php
namespace App\DataProvider\Projet\Projet;

use ApiPlatform\Core\DataProvider\ContextAwareCollectionDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Projet\Projet\Projet;
use App\Repository\Projet\Projet\ProjetRepository;

final class ProjetCollectionDataProvider implements ContextAwareCollectionDataProviderInterface, RestrictedDataProviderInterface
{
    private $_projetRepository;
    public function __construct(
        ProjetRepository $projetRepository
    ) {
        $this->_projetRepository = $projetRepository;
    }

    public function supports(string $resourceClass, string $operationName = null, array $context = []): bool
    {
        return Projet::class === $resourceClass;
    }

    public function getCollection(string $resourceClass, string $operationName = null, array $context = []): iterable
    {
        // Retrieve the blog post collection from somewhere
        $liste_projet = $this->_projetRepository->findAll();

        foreach($liste_projet as $projet){
            yield $projet;
        }
    }
}