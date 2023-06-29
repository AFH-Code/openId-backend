<?php

namespace App\Entity\Projet\Projet;

use App\Repository\Projet\Projet\AccesstokenRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Projet\Projet\Traceconnexion;

/**
 * @ORM\Entity(repositoryClass=AccesstokenRepository::class)
 * @ORM\Table(name="`accesstoken`")
 */
class Accesstoken
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity=Traceconnexion::class, inversedBy="accesstokens")
     * @ORM\JoinColumn(nullable=false)
     */
    private $traceconnexion;

    public function __construct()
    {
      $this->date = new \Datetime();
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

    public function getTraceconnexion(): ?Traceconnexion
    {
        return $this->traceconnexion;
    }

    public function setTraceconnexion(?Traceconnexion $traceconnexion): self
    {
        $this->traceconnexion = $traceconnexion;

        return $this;
    }
}
