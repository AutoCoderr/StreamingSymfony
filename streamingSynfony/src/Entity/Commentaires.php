<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CommentairesRepository")
 */
class Commentaires
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $contenu;

    /**
     * @ORM\Column(type="datetime", length=500)
     */
    private $dateTime;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="commentaires")
     * @ORM\JoinColumn(nullable=false)
     */

    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Films", inversedBy="commentaires")
     * @ORM\JoinColumn(nullable=true)
     */
    private $film;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Episodes", inversedBy="commentaires")
     * @ORM\JoinColumn(nullable=true)
     */
    private $episode;

    public function getId(): ?int
    {
        return $this->id;
    }


    public function getDateTime(): ?\DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime($dateTime): self
    {
        $this->dateTime = $dateTime;
        return $this;
    }



    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    public function setContenu(string $contenu): self
    {
        $this->contenu = $contenu;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->user;
    }

    public function setUser(?Users $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getFilm(): ?Films
    {
        return $this->film;
    }

    public function setFilm(?Films $film): self
    {
        $this->film = $film;

        return $this;
    }

    public function getEpisode(): ?Episodes
    {
        return $this->episode;
    }

    public function setEpisode(?Episodes $episode): self
    {
        $this->episode = $episode;

        return $this;
    }
}
