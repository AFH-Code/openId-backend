<?php

namespace App\Controller\Projet\Projet;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Servicetext\GeneralServicetext;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\Projet\Projet\TraceconnexionRepository;
use App\Repository\Projet\Projet\ProjetRepository;
use App\Service\Projet\Projet\TraceconnexionService;
use App\Utils\ErrorHttp;
use App\Utils\Globals;
use App\Entity\Projet\Projet\Traceconnexion;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use App\Repository\Users\User\UserRepository;

class TraceconnexionController extends AbstractController
{
    private $_traceconnexionRepository;
    private $_projetRepository;
    private $_traceconnexionService;
    private Globals $globals;
    private $_params;
    private $helperService;
    private $_userRepository;

    public function __construct(TraceconnexionRepository $traceconnexionRepository, ProjetRepository $projetRepository,TraceconnexionService $traceconnexionService,
    Globals $globals, ParameterBagInterface $params, GeneralServicetext $helperService, UserRepository $userRepository)
    {
        $this->_traceconnexionRepository = $traceconnexionRepository;
        $this->_projetRepository = $projetRepository;
        $this->_traceconnexionService = $traceconnexionService;
        $this->globals = $globals;
        $this->_params = $params;
        $this->helperService = $helperService;
        $this->_userRepository = $userRepository;
    }

    public function generateaccesstoken(Request $request, GeneralServicetext $service)
    {
      $parameters = json_decode($request->getContent(), true);
        
   		if(count($parameters) == 2)
   		{
          if($service->array_keys_exists(array("clientSecret", "authcode"), $parameters))
          {
               $clientAppli = $this->_projetRepository->findOneBy(array('clientsecret'=>$parameters['clientSecret']));
                
               if($clientAppli != null)
               {
                  $clientconnexion = $service->decrypt($parameters['authcode'], $clientAppli->getClientsecret());
                  $tabconnexion = json_decode($clientconnexion, true);
                  //return new JsonResponse(array("status-code" => $clientconnexion)); 
                  $traceconnexion = null;
                  if($service->array_keys_exists(array("id"), $tabconnexion))
   			          {
                    $traceconnexion = $this->_traceconnexionRepository->find($tabconnexion['id']);
                  }
                  if($traceconnexion != null){
                    $data = $this->_traceconnexionService->generateAccessToken($traceconnexion);
                    return $data;
                  }else{
                    return new JsonResponse(array("status-code" => 500, "description" => "Nous n'avons pas pu identifier l'objet, Vérifiez votre authcode"));
                  }
               }else{
                return new JsonResponse(array("status-code" => 500, "description" => "Nous n'avons pas pu identifier l'objet, Vérifiez votre clientSecret"));
               }               
            }else{
              return $this->globals->error(ErrorHttp::FORM_ERROR);
            }
        }else{
          return $this->globals->error(ErrorHttp::FORM_ERROR);
        }
    }

    public function traceconnexionClientId()
    {
      if(!isset($_GET["clientId"]))
        return $this->globals->error(ErrorHttp::FORM_ERROR);

        $tabClientId = explode(']z12U[', $_GET["clientId"]);

        if(count($tabClientId) == 2)
        {
            $clientIdKey = $tabClientId[0];

            $salt = $this->_params->get('saltcookies');
            $clientUniq = $this->helperService->decrypt($clientIdKey, $salt);
            
            $organization = $this->_projetRepository->myfindOneBy($clientUniq);
            
            if($organization != null)
            {
                $traceconnexion = new Traceconnexion();
                $data = $this->_traceconnexionService->saveNewTrace($traceconnexion, $organization);
                return $data;
            }else{
                return $this->globals->error(ErrorHttp::FORM_ERROR);
            }
        }
    }

    public function updateTraceconnexion(Request $request)
    {
        $idTrace = $request->attributes->get('id');
        $traceconnexion = $this->_traceconnexionRepository->find($idTrace);        
        $data = $this->globals->jsondecode();

        if(!isset($data->iduser) or $traceconnexion == null)
          return $this->globals->error(ErrorHttp::FORM_ERROR);

        $user = $this->_userRepository->find($data->iduser);
        if($user != null)
        {
            $data = $this->_traceconnexionService->UpdateTrace($traceconnexion, $user);
            return $data;
        }else{
          return $this->globals->error(ErrorHttp::FORM_ERROR);
        }
    }

    public function listeTraceconnexion(Request $request)
    {
      $position = $request->attributes->get('position');
      if($this->getUser() == null)
        return $this->globals->error(ErrorHttp::FORM_ERROR);


      if(isset($_GET["page"]))
      {
        $page = $_GET["page"];
      }else{
        $page = 1;
      }
      if(isset($_GET["tail"])){
        $tail = $_GET["tail"];
      }else{
        $tail = 10;
      }

      
      if($position == 'dashboard')
      {
          $liste_traceconnexion = $this->_traceconnexionRepository->myFindByUser($this->getUser()->getId(), $page, $tail);
      }else{
          $liste_traceconnexion = $this->_traceconnexionRepository->findAll();
      }
      return $liste_traceconnexion;
    }
}