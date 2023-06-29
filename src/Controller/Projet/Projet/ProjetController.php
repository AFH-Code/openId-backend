<?php

namespace App\Controller\Projet\Projet;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\Projet\Projet\ProjetRepository;
use App\Entity\Projet\Projet\Projet;
use App\Service\Projet\Projet\ProjetService;
use App\Utils\ErrorHttp;
use App\Utils\Globals;
use App\Service\Projet\Projet\TraceconnexionService;
use App\Service\Servicetext\GeneralServicetext;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Entity\Projet\Projet\Traceconnexion;

use Symfony\Component\HttpFoundation\JsonResponse;

class ProjetController extends AbstractController
{
    private $_projetRepository;
    private $_entityManager;
    private $projetService;
    private Globals $globals;
    private $helperService;
    private $_params;
    private $_traceconnexionService;

    public function __construct(ProjetRepository $_projetRepository, EntityManagerInterface $entityManager, ProjetService $projetService, GeneralServicetext $helperService,
    ParameterBagInterface $params, Globals $globals, TraceconnexionService $traceconnexionService)
    {
        $this->_projetRepository = $_projetRepository;
        $this->_entityManager = $entityManager;
        $this->projetService = $projetService;
        $this->globals = $globals;
        $this->helperService = $helperService;
        $this->_params = $params;
        $this->_traceconnexionService = $traceconnexionService;
    }

    public function addProjet(Request $request)
    {
        $uploadedFile = $request->files->get('file');
        if(!$uploadedFile){
            throw new BadRequestHttpException('"file" is required');
        }

        //$id = $request->attributes->get('id');
        $nom = $request->request->get('nom');
        $description = $request->request->get('description');
        //$user = $this->_projetRepository->find($id); //Sélectionner L'utilisateur par l'id
        
        $projet = $this->projetService->creatProjet($this->getUser(), $nom, $description, $uploadedFile);

        if($projet != null){
            $this->_entityManager->persist($projet);
            $this->_entityManager->flush();
            return new JsonResponse(array("status-code" => 200, "description" => "OK - Projet Créer avec succès", "projet"=>$this->projetService->exposeProjet($projet)), Response::HTTP_OK);
        }else{
            return new JsonResponse(array("status-code" => 400, "description" => "Echec de Création du projet"), Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateProjet(Request $request)
    {
        $uploadedFile = $request->files->get('file');

        if(!$uploadedFile){
            throw new BadRequestHttpException('"file" is required');
        }

        $id = $request->attributes->get('id');
        $nom = $request->request->get('nom');
        $description = $request->request->get('description');
        $projet = $this->_projetRepository->find($id); //Sélectionner L'utilisateur par l'id

        if($projet != null){
            $projet = $this->projetService->updateProjet($projet, $nom, $description, $uploadedFile);
            $this->_entityManager->flush();
            return new JsonResponse(array("status-code" => 200, "description" => "OK - Projet Créer avec succès", "projet"=>$this->projetService->exposeProjet($projet)), Response::HTTP_OK);
        }else{
            return new JsonResponse(array("status-code" => 400, "description" => "Echec de Création du projet"), Response::HTTP_BAD_REQUEST);
        }
    }

    public function generateClientKey(Request $request)
    {
        $id = $request->attributes->get('id');
        $data = json_decode($request->getContent(), true);

        $typeoauth = $data['typeoauth'];
        $connecturl = $data['urlconnexion'];
        $domaineautorise = $data['domaineautorise'];
        
        $projet = $this->_projetRepository->find($id); //Sélectionner L'utilisateur par l'id
        if($projet != null){
            $response = $this->projetService->generateClientKey($projet, $typeoauth, $connecturl, $domaineautorise);
            return $response;
        }else{
            return new JsonResponse(array("status-code" => 400, "description" => "Echec de Création du projet"), Response::HTTP_BAD_REQUEST);
        }
    }

    public function test(Projet $projet)
    {
        /*
        //Génération du code client
        $salt = $this->_params->get('saltcookies');
        $codeIdClient = $this->helperService->encrypt($projet->getId(), $salt);
        
        $keydata = array();
        $keydata['id'] = $projet->getId();
        $keydata['autorisations'] = array('ACCESS_EMAIL','ACCESS_TEL');

        //Genaration du code et accès Utilisateur
        $key = $this->helperService->getPassword(16);
        $jsonkeydata = json_encode($keydata);
        $codeClient = $this->helperService->encrypt($jsonkeydata, $key);

        $projet->setClientid($codeIdClient.']z12U['.$codeClient);
        $projet->setClientsecret($key);

        $this->_entityManager->flush();

        echo $jsonkeydata;
        */

        if($this->getUser() != null)
        {
            $name = $this->getUser()->getFirstname();
            return new Response('<html><header><title>Test</title></header><body>'.$name.'</body></html>');
        }else{
            echo 0;
        }
        exit;
    }
}

