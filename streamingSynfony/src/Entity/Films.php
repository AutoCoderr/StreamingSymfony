<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FilmsRepository")
 */
class Films
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=30)
     */
    private $titre;

    /**
     * @ORM\Column(type="integer")
     */
    private $vues = 0;

    /**
     * @ORM\Column(type="time")
     */
    private $duree;

    /**
     * @ORM\Column(type="string", length=500)
     */
    private $synopsis;

    /**
     * @ORM\Column(type="date")
     */
    private $dateSortie;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $prenomAuteur = null;

    /**
     * @ORM\Column(type="string", length=30, nullable=true)
     */
    private $nomAuteur = null;

    /**
     * @ORM\Column(type="string", length=50)
     */
    private $image;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Users", inversedBy="films")
     */
    private $User;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sagas", inversedBy="films")
     */
    private $Saga;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Categories", inversedBy="Films")
     */
    private $categorie;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\URLs", mappedBy="Film")
     */
    private $uRLs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Commentaires", mappedBy="film")
     */
    private $commentaires;

    public function __construct()
    {
        $this->uRLs = new ArrayCollection();
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

    public function getDuree(): ?\DateTimeInterface
    {
        return $this->duree;
    }

    public function setDuree(\DateTimeInterface $duree): self
    {
        $this->duree = $duree;

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

    public function getSynopsis(): ?string
    {
        return $this->synopsis;
    }

    public function setSynopsis(string $synopsis): self
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getDateSortie(): ?\DateTimeInterface
    {
        return $this->dateSortie;
    }

    public function setDateSortie(\DateTimeInterface $dateSortie): self
    {
        $this->dateSortie = $dateSortie;

        return $this;
    }

    public function getPrenomAuteur(): ?string
    {
        return $this->prenomAuteur;
    }

    public function setPrenomAuteur(string $prenomAuteur): self
    {
        $this->prenomAuteur = $prenomAuteur;

        return $this;
    }

    public function getNomAuteur(): ?string
    {
        return $this->nomAuteur;
    }

    public function setNomAuteur(string $nomAuteur): self
    {
        $this->nomAuteur = $nomAuteur;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

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

    public function getSaga(): ?Sagas
    {
        return $this->Saga;
    }

    public function setSaga(?Sagas $Saga): self
    {
        $this->Saga = $Saga;

        return $this;
    }

    public function getCategorie(): ?Categories
    {
        return $this->categorie;
    }

    public function setCategorie(?Categories $categorie): self
    {
        $this->categorie = $categorie;

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
            $uRL->setFilm($this);
        }

        return $this;
    }

    public function removeURL(URLs $uRL): self
    {
        if ($this->uRLs->contains($uRL)) {
            $this->uRLs->removeElement($uRL);
            // set the owning side to null (unless already changed)
            if ($uRL->getFilm() === $this) {
                $uRL->setFilm(null);
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
}
