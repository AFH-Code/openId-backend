<?php

namespace App\Service\Projet\Projet;

use App\Repository\Projet\Projet\ProjetRepository;
use App\Entity\Projet\Projet\Projet;
use App\Service\Servicetext\GeneralServicetext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Doctrine\ORM\EntityManagerInterface;
use App\Service\Servicetext\FileUploader;
use Symfony\Component\String\Slugger\SluggerInterface;

class ProjetService{
    private $helperService;
    private $_params;
    private $_entityManager;
    private $fileUploader;
    private $slugger;

    public function __construct(
        GeneralServicetext $helperService,
        EntityManagerInterface $entityManager,
        ParameterBagInterface $params,
        FileUploader $fileUploader,
        SluggerInterface $slugger
    )
    {
        $this->helperService = $helperService;
        $this->_params = $params;
        $this->_entityManager = $entityManager;
        $this->fileUploader = $fileUploader;
        $this->slugger = $slugger;
    }

    public function creatProjet($user, $nom, $description, $uploadedFile)
    {
        $projet = new Projet($this->helperService);
        $projet->setNom($nom);
        $safeProjetname = $this->slugger->slug($nom).'-'.uniqid();
        $projet->setProjetUniq($safeProjetname);
        $projet->setDescription($description);
        
        $profilFileName = $this->fileUploader->publicFileUpload($uploadedFile, $projet->getUploadRootDir(), 'image');
        $projet->updateSaveFile($profilFileName);
        $projet->setUser($user);
        return $projet;
    }

    public function updateProjet($projet, $nom, $description, $uploadedFile)
    {
        $projet->setNom($nom);
        $projet->setDescription($description);
        //$safeProjetname = $this->slugger->slug($nom).'-'.uniqid();
        //$projet->setProjetUniq($safeProjetname);
        $projet->setHelperService($this->helperService);
        $profilFileName = $this->fileUploader->publicFileUpload($uploadedFile, $projet->getUploadRootDir(), 'image');
        $projet->updateSaveFile($profilFileName);
        return $projet;
    }

    public function exposeProjet(Projet $projet)
    {
        $path = $this->helperService->getArchiveWebDirectory().''.$projet->getWebPath();

        $projetdata = array();
        $projetdata['id'] = $projet->getId();
        $projetdata['nom'] = $projet->getNom();
        $projetdata['description'] = $projet->getDescription();
        $projetdata['logoprojet'] = $path;
        $projetdata['redirecturl'] = $projet->getRedirecturl();
        $projetdata['domaineautorise'] = $projet->getDomaineautorise();

        return $projetdata;
    }

    public function generateClientKey($projet, $typeoauth, $connecturl, $domaineautorise)
    {
        $projet->setTypeoauth($typeoauth);
        $projet->setRedirecturl($connecturl);
        $projet->setDomaineautorise($domaineautorise);
        
        //Génération du code client
        $salt = $this->_params->get('saltcookies');
        $codeIdClient = $this->helperService->encrypt($projet->getProjetUniq(), $salt);
        
        $keydata = array();
        $keydata['id'] = $projet->getId();
        $keydata['autorisations'] = array('ACCESS_EMAIL','ACCESS_TEL');

        //Genaration du code et accès Utilisateur
        $key = $this->helperService->getPassword(16);
        $jsonkeydata = json_encode($keydata);
        $codeClient = $this->helperService->encrypt($jsonkeydata, $key);

        $projet->setClientid($codeIdClient.']z12U['.$codeClient);
        $projet->setClientsecret($key);

        $this->_entityManager->flush();

        return new JsonResponse(array("status-code" => 200, "description" => "OK - Projet Créer avec succès".$projet->getId().''.$typeoauth, "projet"=>$this->exposeProjet($projet)), Response::HTTP_OK);
    }
}