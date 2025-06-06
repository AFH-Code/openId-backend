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
use App\Service\Sms\SingleSmsService;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class UserController extends AbstractController
{
    private $userService;
    private $helperService;
    private $_entityManager;
    private $_passwordEncoder;
    private Globals $globals;
    private $_servicemail;
    private SingleSmsService $singleSms;
    private $params;

    public function __construct(UserService $userService, GeneralServicetext $helperService, EntityManagerInterface $entityManager, 
    UserPasswordEncoderInterface $passwordEncoder, Globals $globals, Singleemail $servicemail, SingleSmsService $singleSms, ParameterBagInterface $params)
    {
        $this->userService = $userService;
        $this->helperService = $helperService;
        $this->_entityManager = $entityManager;
        $this->_passwordEncoder = $passwordEncoder;
        $this->globals = $globals;
        $this->_servicemail = $servicemail;
        $this->singleSms = $singleSms;
        $this->params = $params;
    }

    public function createaccount(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        if($this->helperService->array_keys_exists(array("firstName", "lastName", "username", "password", "countryCode", "dialCode", "telephone"), $parameters))
        {
            $telephone = $this->helperService->formatPhone(trim($parameters['telephone']));
            $countryCode = strtolower(trim($parameters['countryCode']));
            $oldUser = $this->_entityManager->getRepository(User::class)
					        ->myFindOldUser(trim($parameters['username']), $telephone, $countryCode);
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
                    $user->setPhone($parameters['username']);
                }

                if($user->getPhone() == null and $this->helperService->tel($telephone))
                {
                    $user->setPhone($telephone);
                    $user->setCountryCode($countryCode);
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

    public function resetPasswordCode(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        if($this->helperService->array_keys_exists(array("username"), $parameters))
        {
            $username = $parameters['username'];
            $user = $this->userService->getUserByIndex($username, "username");
            
            if($user != null)
            {
                $code = $this->helperService->getPassword(8);
                $user->setDialCode($code);
                $this->_entityManager->flush();

                $sitename = $this->params->get('sitename');
			    $emailadmin = $this->params->get('emailadmin');
                $siteweb = $this->params->get('siteweb');
                $accountKey = $this->helperService->generateToken($user->getUsername());
                if($this->helperService->email($username))
                {
                    $response = $this->_servicemail->sendNotifEmail(
                        $user->getName(50), 
                        $username, 
                        'Utilisez le code suivant pour renouveller votre mot de passe: '.$code,
                        'Utilisez le code suivant pour renouveller votre mot de passe: '.$code,
                        'Utilisez le code suivant pour renouveller votre mot de passe: '.$code, 
                        $siteweb.'/singlepage/check/code/user?accountKey='.$accountKey
                    );
                }else if($this->helperService->tel($username))
                {
                    $response = $this->singleSms->sendSms(
                        $user->getName(50),
                        $username, 
                        'Utilisez le code suivant pour renouveller votre mot de passe: '.$code,
                        null,
                        null,
                        $user->getCountryCode()
                    );
                }

                $response = $this->_servicemail->sendNotifEmail(
                    $sitename, 
                    $emailadmin, 
                    $user->getName(50).' vient de demander la mise à jour de son mot de passe, son code est le: '.$code,
                    $user->getName(50).' vient de demander la mise à jour de son mot de passe, son code est le: '.$code,
                    $user->getName(50).' vient de demander la mise à jour de son mot de passe, son code est le: '.$code,
                    $siteweb.'/singlepage/check/code/user?accountKey='.$accountKey
                );

                return new JsonResponse(array("status-code" => 200, "description" => "OK - Code envoyé avec succès !", "accountKey"=>$accountKey), Response::HTTP_OK);;
            }else{
                return $this->globals->error(ErrorHttp::FORM_ERROR);
            }
        }else{
            return $this->globals->error(ErrorHttp::FORM_ERROR);
        }
    }

    public function updateAccountKey(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        if(count($parameters) == 3 and $this->helperService->array_keys_exists(array("password","accountkey","code"), $parameters))
        {
            $accountKey = $parameters['accountkey'];
            $username = $this->helperService->resolveToken($accountKey);
            
            $user = $this->_entityManager->getRepository(User::class)
					    ->findOneBy(array("username"=>$username));

            if($user != null and trim($user->getDialCode()) == trim($parameters['code']))
            {
                $passuser = $parameters['password'];
                $salt = substr(crypt($passuser,''), 0, 16);
                $user->setSalt($salt);
                $newpassword = $this->helperService->encrypt($passuser,$salt);
                $user->setPassword($newpassword);
                $this->_entityManager->flush();
                
                return new JsonResponse(array("status-code" => 200, "description" => "OK - Mot de passe mise à jour avec succès "), Response::HTTP_OK);
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

