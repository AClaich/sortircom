<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\StatusRepository")
 */
class Status
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Outing", mappedBy="status")
     */
    private $outings;

    public function __construct()
    {
        $this->outings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return Collection|Outing[]
     */
    public function getOutings(): Collection
    {
        return $this->outings;
    }

    public function addOuting(Outing $outing): self
    {
        if (!$this->outings->contains($outing)) {
            $this->outings[] = $outing;
            $outing->setStatus($this);
        }

        return $this;
    }

    public function removeOuting(Outing $outing): self
    {
        if ($this->outings->contains($outing)) {
            $this->outings->removeElement($outing);
            // set the owning side to null (unless already changed)
            if ($outing->getStatus() === $this) {
                $outing->setStatus(null);
            }
        }

        return $this;
    }
}
