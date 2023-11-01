<?php

namespace App\Entity\Projet\Projet;

use App\Entity\Users\User\User;
use App\Repository\Projet\Projet\ProjetRepository;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
//use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use App\DataPersister\Projet\Projet\CreateMediaObjectAction;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use App\Service\Servicetext\GeneralServicetext;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity()
* @ORM\Entity(repositoryClass=ProjetRepository::class)
 * @ApiResource(
 *     normalizationContext={"groups"={"projet:read"}},
 *     denormalizationContext={"groups"={"projet:write"}}
 * )
 * @Vich\Uploadable
 */
class Projet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("projet:read")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"projet:read", "projet:write", "projet:child"})
     */
    private $nom;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"projet:read", "projet:write"})
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("projet:read")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $logoprojet;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @ApiProperty(iri="https://schema.org/logoprojeturl")
     * @Groups({"projet:read"})
     */
    public $logoprojeturl;

    /**
     * @Vich\UploadableField(mapping="logo_projet", fileNameProperty="logoprojet", size="imageSize", mimeType="mimeType", originalName="originalName")
     * @var File|null
     */
    public ?File $file = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"projet:read"})
     */
    private $imageSize;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"projet:read"})
     */
    private $originalName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $mimeType;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="projets")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $typeoauth;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"projet:read", "projet:write"})
     */
    private $redirecturl;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"projet:read", "projet:write"})
     */
    private $domaineautorise;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"projet:read"})
     */
    private $clientid;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"projet:read"})
    */
    private $clientsecret;

    /**
     * @ORM\OneToMany(targetEntity=Traceconnexion::class, mappedBy="projet")
     */
    private $traceconnexions;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"projet:read"})
    */
    private $projetUniq;

    private $helperService;

    public function __construct(GeneralServicetext $service)
    {
      $this->date = new \Datetime();
      $this->helperService = $service;
      $this->traceconnexions = new ArrayCollection();
    }

    public function postLoad(LifecycleEventArgs $args)
     {
         $entity = $args->getEntity();
         if(method_exists($entity, 'setGeneralServicetext')) {
             $entity->setGeneralServicetext($this->helperService);
         }
     }

     public function setGeneralServicetext(GeneralServicetext $service)
     {
        $this->helperService = $service;
     }

     public function setHelperService(GeneralServicetext $service)
     {
        $this->helperService = $service;
     }

     public function getHelperService()
     {
        return $this->helperService;
     }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getUploadDir($absolute = true)
	{
        // On retourne le chemin relatif vers l'image pour un navigateur
        if($absolute == true)
        {
            return 'uploads\projet\projet\images\logo';
        }else{
            return 'uploads/projet/projet/images/logo';
        }
	}

    public function getUploadRootDir()
	{
        // On retourne le chemin absolu vers l'image pour notre codePHP
        if($this->helperService != null)
        {
            return  $this->helperService->getPublicPath().'\\'.$this->getUploadDir().'\\';
        }else{
            return __DIR__.'/../../../../public/'.$this->getUploadDir(false).'/';
        }
	}

    public function updateSaveFile($imgprofil)
    {
        $tempOldFilename = $this->getUploadRootDir().''.$this->getLogoprojet();

        if($this->getLogoprojet() != null && file_exists($tempOldFilename)){
            // On supprime le fichier
            unlink($tempOldFilename);
        }
        $this->setLogoprojet($imgprofil);
    }
    
    public function getWebPath()
	{
	    return $this->getUploadDir(false).'/'.$this->getLogoprojet();
	}

    public function getLogoprojet(): ?string
    {
        return $this->logoprojet;
    }

    public function setLogoprojet(?string $logoprojet): self
    {
        $this->logoprojet = $logoprojet;

        return $this;
    }

    public function getTypeoauth(): ?string
    {
        return $this->typeoauth;
    }

    public function setTypeoauth(?string $typeoauth): self
    {
        $this->typeoauth = $typeoauth;

        return $this;
    }

    public function getRedirecturl(): ?string
    {
        return $this->redirecturl;
    }

    public function setRedirecturl(?string $redirecturl): self
    {
        $this->redirecturl = $redirecturl;

        return $this;
    }

    public function getDomaineautorise(): ?string
    {
        return $this->domaineautorise;
    }

    public function setDomaineautorise(?string $domaineautorise): self
    {
        $this->domaineautorise = $domaineautorise;

        return $this;
    }

    public function getClientid(): ?string
    {
        return $this->clientid;
    }

    public function setClientid(?string $clientid): self
    {
        $this->clientid = $clientid;

        return $this;
    }

    public function getClientsecret(): ?string
    {
        return $this->clientsecret;
    }

    public function setClientsecret(?string $clientsecret): self
    {
        $this->clientsecret = $clientsecret;

        return $this;
    }

    /**
     * @return Collection|Traceconnexion[]
     */
    public function getTraceconnexions(): Collection
    {
        return $this->traceconnexions;
    }

    public function addTraceconnexion(Traceconnexion $traceconnexion): self
    {
        if (!$this->traceconnexions->contains($traceconnexion)) {
            $this->traceconnexions[] = $traceconnexion;
            $traceconnexion->setProjet($this);
        }

        return $this;
    }

    public function removeTraceconnexion(Traceconnexion $traceconnexion): self
    {
        if ($this->traceconnexions->removeElement($traceconnexion)) {
            // set the owning side to null (unless already changed)
            if ($traceconnexion->getProjet() === $this) {
                $traceconnexion->setProjet(null);
            }
        }

        return $this;
    }

    public function getprojetUniq(): ?string
    {
        return $this->projetUniq;
    }

    public function setprojetUniq(?string $projetUniq): self
    {
        $this->projetUniq = $projetUniq;

        return $this;
    }

    public function getImageSize(): ?string
    {
        return $this->imageSize;
    }

    public function setImageSize(?string $imageSize): self
    {
        $this->imageSize = $imageSize;

        return $this;
    }

    public function getOriginalName(): ?string
    {
        return $this->originalName;
    }

    public function setOriginalName(?string $originalName): self
    {
        $this->originalName = $originalName;

        return $this;
    }

    public function getMimeType(): ?string
    {
        return $this->mimeType;
    }

    public function setMimeType($mimeType =null): self
    {
        $this->mimeType = $mimeType;

        return $this;
    }
}
