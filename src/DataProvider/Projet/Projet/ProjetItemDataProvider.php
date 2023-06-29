<?php
namespace App\DataProvider\Projet\Projet;

use ApiPlatform\Core\DataProvider\ItemDataProviderInterface;
use ApiPlatform\Core\DataProvider\RestrictedDataProviderInterface;
use App\Entity\Projet\Projet\Projet;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Projet\Projet\ProjetRepository;

final class ProjetItemDataProvider implements ItemDataProviderInterface, RestrictedDataProviderInterface
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

    public function getItem(string $resourceClass, $id, string $operationName = null, array $context = []): ?Projet
    {
        // Retrieve the blog post item from somewhere then return it or null if not found
        $projet = $this->_projetRepository->find($id);
        return $projet;
    }
}