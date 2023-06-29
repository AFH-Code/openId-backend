<?php
// src/DataPersister/Projet/Projet/TraceconnexionDataPersister.php

namespace App\DataPersister\Projet\Projet;

use App\Entity\Projet\Projet\Traceconnexion;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\ResumableDataPersisterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Servicetext\GeneralServicetext;
use App\Service\Email\Singleemail;
use App\Service\Projet\Projet\TraceconnexionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Repository\Projet\Projet\ProjetRepository;
use App\Repository\Users\User\UserRepository;
use Symfony\Component\HttpFoundation\Request;

/**
 *
*/
class TraceconnexionDataPersister implements ContextAwareDataPersisterInterface, ResumableDataPersisterInterface
{
    private $_entityManager;
    private $_helperService;
    private $_servicemail;
    private $_params;
    private $_projetRepository;
    private $_traceconnexionService;
    private $_userRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        GeneralServicetext $service,
        ParameterBagInterface $params,
        ProjetRepository $projetRepository,
        UserRepository $userRepository,
        Singleemail $servicemail,
        TraceconnexionService $traceconnexionService
    ) {
        $this->_entityManager = $entityManager;
        $this->_helperService = $service;
        $this->_servicemail = $servicemail;
        $this->_params = $params;
        $this->_projetRepository = $projetRepository;
        $this->_traceconnexionService = $traceconnexionService;
        $this->_userRepository = $userRepository;
    }

    /**
     * {@inheritdoc}
    */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof Traceconnexion;
    }

    /**
     * @param Traceconnexion $data
    */
    public function persist($data, array $context = [])
    {
        
        if($data instanceof Traceconnexion && array_key_exists('collection_operation_name', $context) && (($context['collection_operation_name'] ?? null) == 'post')){
            if($data->getClientid()){
                $tabClientId = explode(']z12U[', $data->getClientid());

                
                if(count($tabClientId) == 2)
                {
                    $clientIdKey = $tabClientId[0];

                    $salt = $this->_params->get('saltcookies');
                    $clientUniq = $this->_helperService->decrypt($clientIdKey, $salt);
                   
                    $organization = $this->_projetRepository->myfindOneBy($clientUniq);
                   
                    if($organization != null)
                    {
                        //return new JsonResponse(array("status-code" => $clientId));
                        $data = $this->_traceconnexionService->saveNewTrace($data, $organization);
                        return $data;
                    }else{
                        return new JsonResponse(array("status-code" => 500, "description" => $clientIdKey." Nous n'avons pas pu identifier l'organisation, Vérifiez votre authcode"));
                    }
                }
            }
        }else{
            if($data instanceof Traceconnexion && array_key_exists('item_operation_name', $context) && (($context['item_operation_name'] ?? null) == 'put')){
                
                $user = $this->_userRepository->find($data->getIduser());
                if($user != null)
                {
                    $data = $this->_traceconnexionService->UpdateTrace($data, $user);
                }else{
                    $data->setTest('Entites Indisponibles');
                }
                
            }
        }
        $this->_entityManager->flush();
        return $data;
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
