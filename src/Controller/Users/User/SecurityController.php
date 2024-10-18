<?php

namespace App\Controller\Users\User;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Servicetext\GeneralServicetext;
use Symfony\Component\HttpFoundation\Request;
use App\Service\Users\User\UserService;
use App\Entity\Users\User\User;
use Symfony\Component\HttpFoundation\JsonResponse;

class SecurityController extends AbstractController
{
    private $userService;
    private $helperService;
    private $_entityManager;

    public function __construct(UserService $userService, GeneralServicetext $helperService, EntityManagerInterface $entityManager)
    {
        $this->userService = $userService;
        $this->helperService = $helperService;
        $this->_entityManager = $entityManager;
    }

    public function login(Request $request)
    {
        $parameters = json_decode($request->getContent(), true);
        if(count($parameters) == 2 and $this->helperService->array_keys_exists(array("username", "password"), $parameters))
        {
            $username = $parameters['username'];
            $password = $parameters['password'];
            $user = $this->userService->getUserByIndex($username); //$this->_entityManager->getRepository(User::class)->find(33);
            $result = $this->userService->checkAccess($user, $password);

            if($result == true)
            {
                if($user->getSalt() == null)
                {
                    $passuser = $password;
                    $salt = substr(crypt($passuser,''), 0, 16);
                    $user->setSalt($salt);
                    $newpassword = $this->helperService->encrypt($passuser,$salt);
                    $user->setPassword($newpassword);
                }
                $this->_entityManager->flush();

                
                $response = $this->userService->getWebToken($user);
                
            }else{
                if($user != null)
                {
                    //Utilisateur trouver mais accès invalide
                    $response = $this->userService->passwordInvalid($user);
                }else{
                    //Utilisateur Non Introuvable
                    $response = $this->userService->usernameInvalid($username);
                }
            }
        }else{
            //Le format de données envoyé est invalide
            $response = $this->userService->fakeLoginData(); 
        }
        $this->helperService->setLoggerMethod(__CLASS__.'::'.__FUNCTION__, 'Recueil information profil utilisateur', 'dfd');

        //return new JsonResponse(array("status-code" => 400));

        /*$return = [
            'status' => 'success',
            'productId' => 1,
        ];

        return new JsonResponse($return, 201);*/

        return $response;
    }
}
