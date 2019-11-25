<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\CategoriesRepository")
 */
class Categories
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
    private $nom;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Films", mappedBy="categorie")
     */
    private $Films;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sagas", mappedBy="categorie")
     */
    private $Sagas;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Series", mappedBy="categorie")
     */
    private $Series;

    public function __construct()
    {
        $this->Films = new ArrayCollection();
        $this->Sagas = new ArrayCollection();
        $this->Series = new ArrayCollection();
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

    /**
     * @return Collection|Films[]
     */
    public function getFilms(): Collection
    {
        return $this->Films;
    }

    public function addFilm(Films $film): self
    {
        if (!$this->Films->contains($film)) {
            $this->Films[] = $film;
            $film->setCategories($this);
        }

        return $this;
    }

    public function removeFilm(Films $film): self
    {
        if ($this->Films->contains($film)) {
            $this->Films->removeElement($film);
            // set the owning side to null (unless already changed)
            if ($film->getCategories() === $this) {
                $film->setCategories(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sagas[]
     */
    public function getSagas(): Collection
    {
        return $this->Sagas;
    }

    public function addSaga(Sagas $saga): self
    {
        if (!$this->Sagas->contains($saga)) {
            $this->Sagas[] = $saga;
            $saga->setCategories($this);
        }

        return $this;
    }

    public function removeSaga(Sagas $saga): self
    {
        if ($this->Sagas->contains($saga)) {
            $this->Sagas->removeElement($saga);
            // set the owning side to null (unless already changed)
            if ($saga->getCategories() === $this) {
                $saga->setCategories(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Series[]
     */
    public function getSeries(): Collection
    {
        return $this->Series;
    }

    public function addSeries(Series $series): self
    {
        if (!$this->Series->contains($series)) {
            $this->Series[] = $series;
            $series->setCategories($this);
        }

        return $this;
    }

    public function removeSeries(Series $series): self
    {
        if ($this->Series->contains($series)) {
            $this->Series->removeElement($series);
            // set the owning side to null (unless already changed)
            if ($series->getCategories() === $this) {
                $series->setCategories(null);
            }
        }

        return $this;
    }
}
