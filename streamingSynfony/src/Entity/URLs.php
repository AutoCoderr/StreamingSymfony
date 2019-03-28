<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\URLsRepository")
 */
class URLs
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
    private $lien;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Films", inversedBy="uRLs")
     */
    private $Film;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Episodes", inversedBy="uRLs")
     */
    private $Episode;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLien(): ?string
    {
        return $this->lien;
    }

    public function setLien(string $lien): self
    {
        $this->lien = $lien;

        return $this;
    }

    public function getFilm(): ?Films
    {
        return $this->Film;
    }

    public function setFilm(?Films $Film): self
    {
        $this->Film = $Film;

        return $this;
    }

    public function getEpisode(): ?Episodes
    {
        return $this->Episode;
    }

    public function setEpisode(?Episodes $Episode): self
    {
        $this->Episode = $Episode;

        return $this;
    }
}
