<?php

namespace App\Controller\Users\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\Users\User\UserService;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Servicetext\GeneralServicetext;
use App\Entity\Users\User\User;
use App\Entity\Users\User\Imgprofil;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Service\Servicetext\FileUploader;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Utils\ErrorHttp;
use App\Utils\Globals;
use App\Service\Email\Singleemail;

class UserController extends AbstractController
{
    private $userService;
    private $helperService;
    private $_entityManager;
    private $_passwordEncoder;
    private Globals $globals;
    private $_servicemail;

    public function __construct(UserService $userService, GeneralServicetext $helperService, EntityManagerInterface $entityManager, 
    UserPasswordEncoderInterface $passwordEncoder, Globals $globals, Singleemail $servicemail)
    {
        $this->userService = $userService;
        $this->helperService = $helperService;
        $this->_entityManager = $entityManager;
        $this->_passwordEncoder = $passwordEncoder;
        $this->globals = $globals;
        $this->_servicemail = $servicemail;
    }

    public function createaccount(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        if($this->helperService->array_keys_exists(array("firstName", "lastName", "username", "password"), $parameters))
        {
            $user = new User();
            $user->setFirstName($parameters['firstName']);
            $user->setLastName($parameters['lastName']);
            $user->setUsername($parameters['username']);
            $user->setpassword(
                $this->_passwordEncoder->encodePassword(
                    $user,
                    $parameters['password']
                )
            );
            if($this->helperService->email($parameters['username']))
            {
              $user->setEmail($parameters['username']);
            }else if($this->helperService->tel($parameters['username']))
            {
              $user->setphone($parameters['username']);
            }
            $user->setUsername($parameters['username']);

            $user->eraseCredentials();

            $tokenInfoUser = $this->userService->generateWebTokenUser($user);
            $tokenInfoToken = $this->userService->generateWebTokenSecurity($user);
            $user->setApiToken($tokenInfoUser.']z12U['.$tokenInfoToken);

            $this->_entityManager->persist($user);
            $this->_entityManager->flush();
            
            $code = $user->getLastvisite()->getTimestamp();
            if($this->helperService->email($user->getUsername()))
            {
                $response = $this->_servicemail->sendNotifEmail($user->getLastName(), $user->getUsername(), 'Vous avez crée votre compte avec succès sur AFHunt', 'Nous sommes heureux de vous compter parmi les nombreux utilisateurs des solutions AFHunt.', 'Le code d\'activation de votre compte est </br> <strong>'.$code.'</strong></br>Cliquez sur le lien ci-dessous pour renseigner le code de validation de votre compte', 'http://myaccount.afhunt.com/');
            }
            $result = $this->userService->accountCreate($user, $code);
            return $result;
        }else{
            return $this->globals->error(ErrorHttp::FORM_ERROR);
        }
    }

    public function validateAccount(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        if(count($parameters) == 2 and $this->helperService->array_keys_exists(array("user_id", "code"), $parameters))
        {
            $userId = $parameters['user_id'];
            $code = $parameters['code'];
            $user = $this->userService->getUserByIndex($userId, "id");

            if($user != null)
            {
                $response = $this->userService->validateAccount($user, $code);
                return $response;
            }else{
                return $this->globals->error(ErrorHttp::FORM_ERROR);
            }
        }else{
            return $this->globals->error(ErrorHttp::FORM_ERROR);
        }
    }

    /**
     * @Route("/update/user/{id}", name="users_user_user_update_account", methods={"POST"}, requirements={"page"="\d+"})
    */
    public function updateUserAccount(User $user, Request $request, ValidatorInterface $validator, FileUploader $fileUploader)
    {
        if(isset($_FILES['imgprofil']) && $_FILES['imgprofil']['error'] == 0)
        {
            $uploadedFile = $request->files->get('imgprofil');
            $user->setService($this->helperService);
            $profilFileName = $fileUploader->publicFileUpload($uploadedFile, $user->getUploadRootDir(), 'image');
            $user->updateSaveFile($profilFileName);

            $firstname = '';
            $lastname = '';

            if(isset($_POST['nom']) and isset($_POST['prenom']))
            {
                $firstname = $_POST['nom'];
                $lastname = $_POST['prenom'];
                $user->setFirstName($firstname);
                $user->setLastName($lastname);
            }
            
            $this->_entityManager->flush();

            $path = $this->helperService->getArchiveWebDirectory().''.$user->getWebPath();
            return new JsonResponse(array("status-code" => 200, "description" => "OK - Compte Créer avec succès; Nom du fichier: ", "imgprofil"=>$path, 'firstname'=>$firstname, 'lastname'=>$lastname), Response::HTTP_OK);
        }

        return new JsonResponse(array("status-code" => 200, "description" => "OK - Compte Créer avec succès"), Response::HTTP_OK);
    }
}

