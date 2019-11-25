<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\EpisodesRepository")
 */
class Episodes
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $titre;

    /**
     * @ORM\Column(type="time")
     */
    private $duree;

    /**
     * @ORM\Column(type="integer")
     */
    private $vues = 0;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $synopsis;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="episodes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $User;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\URLs", mappedBy="Episode")
     */
    private $uRLs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Commentaires", mappedBy="episode")
     */
    private $commentaires;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Saisons", inversedBy="episodes")
     * @ORM\JoinColumn(nullable=false)
     */
    private $saison;

    public function __construct()
    {
        $this->uRLs = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;

        return $this;
    }

    public function getVues(): ?int
    {
        return $this->vues;
    }

    public function setVues(int $vues): self
    {
        $this->vues = $vues;

        return $this;
    }

    public function getDuree(): ?\DateTimeInterface
    {
        return $this->duree;
    }

    public function setDuree(\DateTimeInterface $duree): self
    {
        $this->duree = $duree;

        return $this;
    }

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getUser(): ?Users
    {
        return $this->User;
    }

    public function setUser(?Users $User): self
    {
        $this->User = $User;

        return $this;
    }

    /**
     * @return Collection|URLs[]
     */
    public function getURLs(): Collection
    {
        return $this->uRLs;
    }

    public function addURL(URLs $uRL): self
    {
        if (!$this->uRLs->contains($uRL)) {
            $this->uRLs[] = $uRL;
            $uRL->setEpisode($this);
        }

        return $this;
    }

    public function removeURL(URLs $uRL): self
    {
        if ($this->uRLs->contains($uRL)) {
            $this->uRLs->removeElement($uRL);
            // set the owning side to null (unless already changed)
            if ($uRL->getEpisode() === $this) {
                $uRL->setEpisode(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Commentaires[]
     */
    public function getCommentaires(): Collection
    {
        return $this->commentaires;
    }

    public function addCommentaire(Commentaires $commentaire): self
    {
        if (!$this->commentaires->contains($commentaire)) {
            $this->commentaires[] = $commentaire;
            $commentaire->setEpisode($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): self
    {
        if ($this->commentaires->contains($commentaire)) {
            $this->commentaires->removeElement($commentaire);
            // set the owning side to null (unless already changed)
            if ($commentaire->getEpisode() === $this) {
                $commentaire->setEpisode(null);
            }
        }

        return $this;
    }

    public function getSaison(): ?Saisons
    {
        return $this->saison;
    }

    public function setSaison(?Saisons $saison): self
    {
        $this->saison = $saison;

        return $this;
    }
}
