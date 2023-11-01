<?php
namespace App\Service\Users\User;

use App\Repository\Users\User\UserRepository;
use App\Service\Servicetext\GeneralServicetext;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Users\User\User;

class UserService extends AbstractController{

    private $userRepository;
    private $helperService;
    private $_passwordEncoder;
    private $_params;
    private $_entityManager;

    public function __construct(
        UserRepository $userRepository,
        GeneralServicetext $helperService,
        UserPasswordEncoderInterface $passwordEncoder,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params
    )
    {
        $this->userRepository = $userRepository;
        $this->helperService = $helperService;
        $this->_passwordEncoder = $passwordEncoder;
        $this->_params = $params;
        $this->_entityManager = $entityManager;
    }

    function getUserByIndex($index, $type="username") //Get User|Null by Index
    {
        if($type == "username")
        {
            if($this->helperService->email($index) and $index != null)
            {
                $user = $this->userRepository->findOneBy(array('email'=>$index));
            }else if($this->helperService->tel($index) and $index != null)
            {
                $user = $this->userRepository->findOneBy(array('phone'=>$index));
            }else{
                $user = $this->userRepository->findOneBy(array('username'=>$index));
            }
        }else{
            $user = $this->userRepository->find($index);
        }
        
        return $user;
    }

    function checkAccess($user, $password)
    {
        if($user != null)
        {
            return $this->_passwordEncoder->isPasswordValid($user, $password);
        }else{
            return false;
        } 
    }

    /*
      Utilisateur trouver et authentifié
      ### Pas de contenu | HTTP_OK  200
    */
    function getWebToken($user)
    {
        $token = $user->getApiToken();
        //$token = $this->decriptToken($token);

        $userdata = array();
        $userdata['id'] = $user->getId();
        if($user->getEmail() != null)
        {
            $userdata['username'] = $user->getEmail();
        }else if($user->getPhone() != null)
        {
            $userdata['username'] = $user->getPhone();
        }else{
            $userdata['username'] = $user->getUsername();
        }
        $userdata['firstname'] = $user->getFirstName();
        $userdata['lastname'] = $user->getLastName();
        $userdata['roles'] = $user->getRoles();
        $userdata['connected_user'] = 1;
        $userdata['imgprofil'] = $user->getImgprofil();

        return new JsonResponse(array("status-code" => 200, "description" => "OK - Connexion effectuee avec succes", 
        "token"=>$token, "user"=>$userdata), Response::HTTP_OK);
    }

    /*
        Création du compte effctuée avec succès !
    */
    function accountCreate($user, $status=0, $notif="email")
    {
        $userdata = array();
        $userdata['id'] = $user->getId();
        if($user->getEmail() != null)
        {
            $userdata['username'] = $user->getEmail();
        }else if($user->getPhone() != null)
        {
            $userdata['username'] = $user->getPhone();
        }else{
            $userdata['username'] = $user->getUsername();
        }
        $userdata['firstname'] = $user->getFirstName();
        $userdata['lastname'] = $user->getLastName();
        $userdata['roles'] = $user->getRoles();
        $userdata['notificationtype'] = 'email';
        $userdata['notificationstatus'] = $status;
        $userdata['imgprofil'] = '';

        return new JsonResponse(array("status-code" => 200, "description" => "OK - Compte Créer avec succès", "user"=>$userdata), Response::HTTP_OK);
    }

    function updateUser(User $user, $nom, $prenom, $imageUpload)
    {
        $user->setLastName($nom);
        $user->setFirstName($prenom);

        $user->file = $imageUpload;

        return $user;
    }

    /*
      Utilisateur trouver mais accès invalide
      ### Pas de contenu | HTTP_UNAUTHORIZED  401
    */
    function passwordInvalid($user)
    {
        return new JsonResponse(array("status-code" => 203, "description" => "Echec :".$user->getName(30)." trouve, mais mot de passe invalide"), Response::HTTP_UNAUTHORIZED);
    }

    /*
        Utilisateur Non trouvé
        ### Utilisateur Introuvable | HTTP_NOT_FOUND 404
    */
    function usernameInvalid($username)
    {
        return new JsonResponse(array("status-code" => 204, "description" => "Echec :".$username." ne correspond à aucun utilisateur"), Response::HTTP_NOT_FOUND);
    }

    /*
        Format de données invalide
        ###Statut: Format de donnee non supporte | HTTP_BAD_REQUEST 400
    */
    function fakeLoginData($chaine = "")
    {
        return new JsonResponse(array("status-code" => 400, "description" => "Bad Request - Format de données invalide"), Response::HTTP_BAD_REQUEST);
    }

    function generateWebTokenUser($user)
    {
        $tab = array();
        $tab['title'] = "Informations sur l'utilisateur";
        $tab['username'] = $user->getName(50);
        $token = json_encode($tab);

        $salt = $this->_params->get('saltcookies');
	    $token = $this->helperService->encrypt($token, $salt);

        return $token;
    }

    function generateWebTokenSecurity($user)
    {
        $tab = array();
        $tab['title'] = "Informations sur le token";
        $tab['postagent'] = "chrome";
        $tab['postcountry'] = "Cameroun";
        $token = json_encode($tab);

        $salt = $this->_params->get('saltcookies');
	    $token = $this->helperService->encrypt($token, $salt);

        return $token;
    }

    function decriptToken($token)
    {
        $tabToken = explode(']z12U[', $token);
        $tokenInfouser = $tabToken[0];
        $tokenInfoToken = $tabToken[1];

        $salt = $this->_params->get('saltcookies');
	    $tokenInfouser = $this->helperService->decrypt($tokenInfouser, $salt);
        $tabInfoUser = json_decode($tokenInfouser, true);

        $tokenInfoToken = $this->helperService->decrypt($tokenInfoToken, $salt);
        $tabInfoToken = json_decode($tokenInfoToken, true);

        $description1 = $tabInfoUser['title'];
        $description2 = $tabInfoToken['title'];
        /*
            La suite ici
        */
        return $description1.'-'.$description2;
    }

    function validateAccount($user, $code)
    {
        $userCode = $user->getLastvisite()->getTimestamp();
        if($userCode == $code)
        {
            //Succès 
            $user->setValidaccount(true);
            $this->_entityManager->flush();
            return $this->accountCreate($user);
        }else{
            //Echec
            return $this->usernameInvalid($code);
        }
    }

    public function getConnectUser()
    {
        return $this->getUser();
    }
}
