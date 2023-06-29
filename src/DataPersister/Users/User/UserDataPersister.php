<?php
// src/DataPersister/Users/User/UserDataPersister.php

namespace App\DataPersister\Users\User;

use App\Entity\Users\User\User;
use Doctrine\ORM\EntityManagerInterface;
use ApiPlatform\Core\DataPersister\ContextAwareDataPersisterInterface;
use ApiPlatform\Core\DataPersister\ResumableDataPersisterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Service\Servicetext\GeneralServicetext;
use App\Service\Email\Singleemail;
use App\Service\Users\User\UserService;
use Symfony\Component\HttpFoundation\JsonResponse;

/**
 *
*/
class UserDataPersister implements ContextAwareDataPersisterInterface, ResumableDataPersisterInterface
{
    private $_entityManager;
    private $_passwordEncoder;
    private $_service;
    private $_servicemail;
    private $helperService;
    private $userService;

    public function __construct(
        EntityManagerInterface $entityManager,
        UserPasswordEncoderInterface $passwordEncoder,
        GeneralServicetext $service,
        Singleemail $servicemail,
        UserService $userService
    ) {
        $this->_entityManager = $entityManager;
        $this->_passwordEncoder = $passwordEncoder;
        $this->_service = $service;
        $this->_servicemail = $servicemail;
        $this->userService = $userService;
    }

    /**
     * {@inheritdoc}
    */
    public function supports($data, array $context = []): bool
    {
        return $data instanceof User;
    }

    /**
     * @param User $data
    */
    public function persist($data, array $context = [])
    {
        if($data->getPlainPassword()){
            $data->setPassword(
                $this->_passwordEncoder->encodePassword(
                    $data,
                    $data->getPlainPassword()
                )
            );
            $data->eraseCredentials();
        }
        if ($data->getFakePseudo()) {
            if($this->_service->email($data->getFakePseudo()))
            {
              $data->setEmail($data->getFakePseudo());
            }else if($this->_service->tel($data->getFakePseudo()))
            {
              $data->setphone($data->getFakePseudo());
            }
            $data->setUsername($data->getFakePseudo());
        }
        $tokenInfoUser = $this->userService->generateWebTokenUser($data);
        $tokenInfoToken = $this->userService->generateWebTokenSecurity($data);
        $data->setApiToken($tokenInfoUser.']z12U['.$tokenInfoToken);

        $this->_entityManager->persist($data);
        $this->_entityManager->flush();
        
        $code = $data->getLastvisite()->getTimestamp();
        $response = $this->_servicemail->sendNotifEmail($data->getLastName(), $data->getUsername(), 'Vous avez crée votre compte avec succès sur AFHunt', 'Nous sommes heureux de vous compter parmi les nombreux utilisateurs des solutions AFHunt.', 'Le code d\'activation de votre compte est </br> <strong>'.$code.'</strong></br>Cliquez sur le lien ci-dessous pour renseigner le code de validation de votre compte', 'http://myaccount.afhunt.com/');

        $result = $this->userService->accountCreate($data, $code);
        return $result;
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
