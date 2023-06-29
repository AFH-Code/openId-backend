<?php
namespace App\Service\Projet\Projet;

use App\Repository\Projet\Projet\ProjetRepository;
use App\Entity\Projet\Projet\Projet;
use App\Entity\Users\User\User;
use App\Service\Servicetext\GeneralServicetext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\Projet\Projet\Traceconnexion;
use App\Repository\Projet\Projet\TraceconnexionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Projet\Projet\Accesstoken;

class TraceconnexionService extends AbstractController{
    private $helperService;
    private $_params;
    private $_entityManager;
    private $_traceconnexionRepository;

    public function __construct(
        GeneralServicetext $helperService,
        EntityManagerInterface $entityManager,
        TraceconnexionRepository $traceconnexionRepository,
        ParameterBagInterface $params
    )
    {
        $this->helperService = $helperService;
        $this->_params = $params;
        $this->_entityManager = $entityManager;
        $this->_traceconnexionRepository = $traceconnexionRepository;
    }

    public function saveNewTrace(Traceconnexion $traceconnexion, Projet $projet)
    {
        $traceconnexion->setDemande(true);
        $traceconnexion->setProjet($projet);

        $this->_entityManager->persist($traceconnexion);
        $this->_entityManager->flush();

        return $traceconnexion;
    }

    public function UpdateTrace(Traceconnexion $traceconnexion, User $user)
    {
        //On ferme la connexion demandée précedemment par cet utilisateur afin d'assigner encore une nouvelle connexion.
        $oldconnexion = $this->_traceconnexionRepository->findOneBy(array('projet'=>$traceconnexion->getProjet(), 'user'=>$user));
        if($oldconnexion != null)
        {
            $oldconnexion->setActive(false);
            $oldconnexion->setCloseconnexiondate(new \Datetime());
        }

        $traceconnexion->setUser($user);

        $keydata = array();
        $keydata['id'] = $traceconnexion->getId();
        $keydata['autorisationsAccept'] = array('ACCESS_EMAIL','ACCESS_TEL');

        //Genaration du code et accès Utilisateur
        //$key = $this->helperService->getPassword(16);
        $jsonkeydata = json_encode($keydata);
        $codeClient = $this->helperService->encrypt($jsonkeydata, $traceconnexion->getProjet()->getClientsecret());

        $traceconnexion->setAuthcode($codeClient);
        $traceconnexion->setValidation(true);

        $this->_entityManager->flush();
        return $traceconnexion;
    }

    public function errorToCreate(Traceconnexion $traceconnexion)
    {
        return new JsonResponse(array("status-code" => 400, "description" => "Bad Request - Format de données invalide"), Response::HTTP_BAD_REQUEST);
    }

    public function generateAccessToken(Traceconnexion $traceconnexion)
    {
        $accesstoken = new Accesstoken();
        $accesstoken->setTraceconnexion($traceconnexion);

        $roles = array('ACCESS_EMAIL','ACCESS_TEL');//Ces valeurs seront récupérer depuis le AuthCode de la trace de connexion.

        $userdata = $this->exposeUserData($traceconnexion->getUser(), $this->_params->get("saltcookies"), $roles);
        
        $jsonkeydata = json_encode($userdata);
        $tokencode = $this->helperService->encrypt($jsonkeydata, $this->_params->get("saltcookies"));
        $traceconnexion->setAccesstoken($tokencode);

        $this->_entityManager->persist($accesstoken);
        $this->_entityManager->flush();

        return new JsonResponse(array("status-code" => 200, "description" => "OK - Compte Créer avec succès", "user"=>$userdata, 'accesstoken'=>$tokencode, 'authcode'=>$traceconnexion->getAuthcode()));
    }

    public function exposeUserData(User $user, $key, $roles)
    {
        $path = $this->helperService->getArchiveWebDirectory().''.$user->getWebPath();

        $userdata = array();
        $userdata['id'] = $user->getId();
        $userdata['firstName'] = $user->getFirstName();
        $userdata['lastName'] = $user->getLastName();
        $userdata['email'] = $user->getEmail();
        $userdata['phone'] = $user->getPhone();
        $userdata['imgprofil'] = $path;
        $userdata['tokenkey'] = $key;
        $userdata['autorisationsAccept'] = $roles;

        return $userdata;
    }
}