<?php

namespace App\Entity\Projet\Projet;

use App\Entity\Users\User\User;
use App\Repository\Projet\Projet\TraceconnexionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\SerializedName;
use App\Entity\Projet\Projet\Accesstoken;

/**
 * @ORM\Entity(repositoryClass=TraceconnexionRepository::class)
 * @ORM\Table(name="`traceconnexion`")
 * @ApiResource(
 *    normalizationContext={"groups"={"traceconnexion:read", "projet:read"}},
 *    denormalizationContext={"groups"={"traceconnexion:write"}}
 * )
 */
class Traceconnexion
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"traceconnexion:read"})
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"traceconnexion:read"})
     */
    private $date;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"traceconnexion:read"})
     */
    private $demande;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"traceconnexion:read"})
     */
    private $validation;

    /**
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"traceconnexion:read"})
     */
    private $authcode;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $accesstoken;

    /**
     * @Groups({"traceconnexion:write"})
    */
    private $clientid;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="traceconnexions")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Projet::class, inversedBy="traceconnexions")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"projet:read"})
     */
    private $projet;

    /**
     * @ORM\Column(type="boolean")
     * @Groups({"traceconnexion:read"})
     */
    private $active;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"traceconnexion:read"})
     */
    private $closeconnexiondate;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $test;

    /**
     * @Groups({"traceconnexionput:write"})
     * @SerializedName("iduser")
    */
    private $iduser;

    /**
     * @ORM\OneToMany(targetEntity=Accesstoken::class, mappedBy="traceconnexion", orphanRemoval=true)
     */
    private $accesstokens;

    public function __construct()
    {
      $this->date = new \Datetime();
      $this->demande = false;
      $this->validation = false;
      $this->active = true;
      $this->authcode = '-';
      $this->test = "-";
      $this->accesstokens = new ArrayCollection();
    }

    public function getIduser(): ?int
    {
        return $this->iduser;
    }

    public function setIduser(int $iduser): self
    {
        $this->iduser = $iduser;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getDemande(): ?bool
    {
        return $this->demande;
    }

    public function setDemande(bool $demande): self
    {
        $this->demande = $demande;

        return $this;
    }

    public function getValidation(): ?bool
    {
        return $this->validation;
    }

    public function setValidation(bool $validation): self
    {
        $this->validation = $validation;

        return $this;
    }

    public function getAuthcode(): ?string
    {
        return $this->authcode;
    }

    public function setAuthcode(?string $authcode): self
    {
        $this->authcode = $authcode;

        return $this;
    }

    public function getAccesstoken(): ?string
    {
        return $this->accesstoken;
    }

    public function setAccesstoken(?string $accesstoken): self
    {
        $this->accesstoken = $accesstoken;

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

    public function getProjet(): ?Projet
    {
        return $this->projet;
    }

    public function setProjet(?Projet $projet): self
    {
        $this->projet = $projet;

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

    public function getActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): self
    {
        $this->active = $active;

        return $this;
    }

    public function getCloseconnexiondate(): ?\DateTimeInterface
    {
        return $this->closeconnexiondate;
    }

    public function setCloseconnexiondate(?\DateTimeInterface $closeconnexiondate): self
    {
        $this->closeconnexiondate = $closeconnexiondate;

        return $this;
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function setTest(?string $test): self
    {
        $this->test = $test;

        return $this;
    }

    /**
     * @return Collection|Accesstoken[]
     */
    public function getAccesstokens(): Collection
    {
        return $this->accesstokens;
    }

    public function addAccesstoken(Accesstoken $accesstoken): self
    {
        if (!$this->accesstokens->contains($accesstoken)) {
            $this->accesstokens[] = $accesstoken;
            $accesstoken->setTraceconnexion($this);
        }

        return $this;
    }

    public function removeAccesstoken(Accesstoken $accesstoken): self
    {
        if ($this->accesstokens->removeElement($accesstoken)) {
            // set the owning side to null (unless already changed)
            if ($accesstoken->getTraceconnexion() === $this) {
                $accesstoken->setTraceconnexion(null);
            }
        }

        return $this;
    }
}
