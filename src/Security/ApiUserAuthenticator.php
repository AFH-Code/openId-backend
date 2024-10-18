<?php
namespace App\Security;

use App\Entity\Users\User\User;
use Doctrine\ORM\EntityManagerInterface;

use App\Security\TokenAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class ApiUserAuthenticator
{
    private $authenticator;
    private $guardHandler;

    public function __construct(TokenAuthenticator $authenticator, GuardAuthenticatorHandler $guardHandler)
    {
        $this->authenticator = $authenticator;
        $this->guardHandler = $guardHandler;
    }

    public function userAuthenticator(Request $request)
    {
        // ...
        // after validating the user and saving them to the database
        // authenticate the user and use onAuthenticationSuccess on the authenticator

        $user = new User();
        $user->setFirstName('Kenfack 455');
        $user->setLastName('Blaise');
        $user->setPassword('dfdfd');
        $user->setEmail('noel.kenfack@yahoo.fr'); 
        $roles[] = 'ROLE_USER';
        $user->setRoles($roles);
        $user->setId(2);


        /*$response = $this->guardHandler->authenticateUserAndHandleSuccess(
            $user,          // the User object you just created
            $request,
            $this->authenticator, // authenticator whose onAuthenticationSuccess you want to use
            'main'          // the name of your firewall in security.yaml
        );*/
    }

    public function onKernelRequest(RequestEvent $event)//cette Méthode s'éxécute à chaque requête.
    {
        $request = $event->getRequest();
        //$this->userAuthenticator($request);//Tentative de connexion utilisateur.
    }
}
