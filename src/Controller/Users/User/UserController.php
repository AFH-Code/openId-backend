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

class UserController extends AbstractController
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

    /**
      *@Route("/validate/account", name="users_user_user_validate_account", methods={"POST"})
    */
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
            }else{
                $response = $this->userService->usernameInvalid($code);
            }
        }else{
            $response = $this->userService->fakeLoginData();
        }
        return $response;
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

