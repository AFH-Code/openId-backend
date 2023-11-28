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
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
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
            $oldUser = $this->_entityManager->getRepository(User::class)
					      ->myFindOldUser(trim($parameters['username']));
            if($oldUser == null)
            {
                $user = new User();
                $user->setFirstName($parameters['firstName']);
                $user->setLastName($parameters['lastName']);
                $user->setUsername(trim($parameters['username']));
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
                return $this->globals->error(['message' => 'error, Un utilisateur existe avec cet identifiant', 'code' => 500]);
            }
        }else{
            return $this->globals->error(['message' => 'error, Formulaire Invalide', 'code' => 500]);
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

    public function updateUserAccount(Request $request, ValidatorInterface $validator, FileUploader $fileUploader)
    {
        $uploadedFile = $request->files->get('imgprofil');
        if(!$uploadedFile){
            throw new BadRequestHttpException('"file" is required');
        }
        
        $nom = $request->request->get('nom');
        $prenom = $request->request->get('prenom');
        $id = $request->attributes->get('id');
        
        $oldUser = $this->_entityManager->getRepository(User::class)
					    ->find($id);

        if($oldUser != null){
            
            $oldUser == $this->userService->updateUser($oldUser, $nom, $prenom, $uploadedFile);
            $this->_entityManager->flush();
            $path = $this->helperService->getArchiveWebDirectory().''.$oldUser->getWebPath();
            return new JsonResponse(array("status-code" => 200, "description" => "OK - Compte Créer avec succès; Nom du fichier: ", "imgprofil"=>$path, 'firstname'=>$prenom, 'lastname'=>$nom), Response::HTTP_OK);
            
        }else{
            return new JsonResponse(array("status-code" => 400, "description" => "Echec de modification du profil"), Response::HTTP_BAD_REQUEST);
        }
    }

    public function resetContact(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        if(count($parameters) == 2 and $this->helperService->array_keys_exists(array("email", "telephone"), $parameters))
        {
            $email = $parameters['email'];
            $telephone = $parameters['telephone'];
        
            $oldUser = $this->getUser();

            if($oldUser != null)
            {
                if($this->helperService->email($email))
                {
                    $oldUser->setEmail($email);
                }
                if($this->helperService->tel($telephone))
                {
                    $oldUser->setPhone($telephone);
                }
                $this->_entityManager->flush();
                return $this->globals->success(array('email'=>$email, 'telephone'=>$telephone),'Mise à jour effectuée avec succès');
            }else{
                return $this->globals->error(ErrorHttp::FORM_ERROR); 
            }
        }else{
            return $this->globals->error(ErrorHttp::FORM_ERROR);
        }
    }
}

