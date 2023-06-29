<?php

namespace App\Entity\Users\User;

use App\Entity\Projet\Projet\Projet;
use App\Repository\Users\User\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\SerializedName;

use App\Validator\Validatortext\Taillemin;
use App\Validator\Validatortext\Taillemax;
use App\Validator\Validatortext\Email;
use App\Validator\Validatortext\Telephone;
use App\Validator\Validatortext\Telormail;
use App\Validator\Validatortext\Password;
use App\Validator\Validatortext\Pseudo;
use App\Service\Servicetext\GeneralServicetext;
use App\Entity\Projet\Projet\Traceconnexion;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @ApiResource(
 *    normalizationContext={"groups"={"user:read"}},
 *    denormalizationContext={"groups"={"user:write"}}
 * )
 * @ORM\HasLifecycleCallbacks
 */
class User implements UserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("user:read")
    */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, nullable=true)
     * @Groups({"user:read"})
    */
    private $username;

    /**
     * @Groups("user:write")
     * @SerializedName("username")
     * @Telormail()
    */
    private $fakePseudo;

    /**
     * @ORM\Column(type="json")
     * @Groups("user:read")
    */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     * @Groups("user:write")
    */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"user:read", "user:write"})
     *@Taillemin(valeur= 2, message="Au moins 2 caractères")
     *@Taillemax(valeur= 80, message="Au plus 80 caractères")
     */
    private $firstName;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read", "user:write"})
     * @Taillemin(valeur= 2, message="Au moins 2 caractères")
     * @Taillemax(valeur= 80, message="Au plus 80 caractères")
    */
    private $lastName;

    /**
     * @Groups("user:write")
     * @SerializedName("password")
     * @Password()
    */
    private $plainPassword;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read"})
     * @Email()
    */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user:read"})
     * @Telephone()
    */
    private $phone;

    /**
     * @ORM\Column(type="boolean", nullable=true)
     */
    private $validaccount;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $lastvisite;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $begindate;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $apiToken;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
    */
    private $imgprofil;

    private $service;

    /**
     * @ORM\OneToMany(targetEntity=Projet::class, mappedBy="user", orphanRemoval=true)
    */
    private $projets;

    /**
     * @ORM\OneToMany(targetEntity=Traceconnexion::class, mappedBy="user")
    */
    private $traceconnexions;

    public function __construct()
    {
      $this->roles[] = 'ROLE_USER';
      $this->begindate = new \Datetime();
      $this->lastvisite = new \Datetime();
      $this->validaccount = false;
      $this->projets = new ArrayCollection();
      $this->traceconnexions = new ArrayCollection();
    }

    public function getService()
    {
        return $this->service;
    }

    public function setService(GeneralServicetext $service)
    {
        $this->service = $service;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }
    
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getFakePseudo(): ?string
    {
        return $this->fakePseudo;
    }

    public function setFakePseudo(string $fakePseudo): self
    {
        $this->fakePseudo = $fakePseudo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getValidaccount(): ?bool
    {
        return $this->validaccount;
    }

    public function setValidaccount(bool $validaccount): self
    {
        $this->validaccount = $validaccount;

        return $this;
    }

    public function getLastvisite(): ?\DateTimeInterface
    {
        return $this->lastvisite;
    }

    public function setLastvisite(\DateTimeInterface $lastvisite): self
    {
        $this->lastvisite = $lastvisite;

        return $this;
    }

    public function getBegindate(): ?\DateTimeInterface
    {
        return $this->begindate;
    }

    public function setBegindate(\DateTimeInterface $begindate): self
    {
        $this->begindate = $begindate;

        return $this;
    }

    public function getApiToken(): ?string
    {
        return $this->apiToken;
    }

    public function setApiToken(string $apiToken): self
    {
        $this->apiToken = $apiToken;

        return $this;
    }

    public function getName($tail)
	{
		if($this->firstName != null or $this->lastName != null)
		{
			$allname = $this->firstName.' '.$this->lastName;
			if(strlen($allname) <= $tail)
			{
			    return $allname;
			}else{
                $text = wordwrap($allname,$tail,'~',true);
                $tab = explode('~',$text);
                $text = $tab[0];
                return $text.'...';
			}
		}else{
			return $this->getPid();
		}
	}

    public function getImgprofil(): ?string
    {
        return $this->imgprofil;
    }

    public function setImgprofil(string $imgprofil): self
    {
        $this->imgprofil = $imgprofil;

        return $this;
    }

    public function getUploadDir($absolute = true)
	{
        // On retourne le chemin relatif vers l'image pour un navigateur
        if($absolute == true)
        {
            return 'uploads\users\user\images\profil';
        }else{
            return 'uploads/users/user/images/profil';
        }
	}

    public function getUploadRootDir()
	{
        // On retourne le chemin absolu vers l'image pour notre codePHP
        if($this->service != null)
        {
            return  $this->service->getPublicPath().'\\'.$this->getUploadDir().'\\';
        }else{
            return __DIR__.'/../../../../public/'.$this->getUploadDir(false).'/';
        }
	}

    public function updateSaveFile($imgprofil)
    {
        $tempOldFilename = $this->getUploadRootDir().''.$this->getImgProfil();

        if($this->getImgProfil() != null && file_exists($tempOldFilename)){
            // On supprime le fichier
            unlink($tempOldFilename);
        }
        $this->setImgProfil($imgprofil);
    }
    
    public function getWebPath()
	{
	    return $this->getUploadDir(false).'/'.$this->getImgprofil();
	}

    /**
	*@ORM\PreRemove()
	*/
	public function preRemoveUpload()
    {
        $tempOldFilename = $this->getUploadRootDir().''.$this->getImgProfil();
        if(file_exists($tempOldFilename)){
            // On supprime le fichier
            unlink($tempOldFilename);
        }
    }

    /**
     * @return Collection|Projet[]
     */
    public function getProjets(): Collection
    {
        return $this->projets;
    }

    public function addProjet(Projet $projet): self
    {
        if (!$this->projets->contains($projet)) {
            $this->projets[] = $projet;
            $projet->setUser($this);
        }

        return $this;
    }

    public function removeProjet(Projet $projet): self
    {
        if ($this->projets->removeElement($projet)) {
            // set the owning side to null (unless already changed)
            if ($projet->getUser() === $this) {
                $projet->setUser(null);
            }
        }

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
            $traceconnexion->setUser($this);
        }

        return $this;
    }

    public function removeTraceconnexion(Traceconnexion $traceconnexion): self
    {
        if ($this->traceconnexions->removeElement($traceconnexion)) {
            // set the owning side to null (unless already changed)
            if ($traceconnexion->getUser() === $this) {
                $traceconnexion->setUser(null);
            }
        }

        return $this;
    }
}
