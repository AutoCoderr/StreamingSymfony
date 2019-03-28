<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UsersRepository")
 * @UniqueEntity("email")
 */
class Users implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=50)
     * @Assert\NotBlank
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=20)
     * @Assert\NotBlank
     */
    private $nom;

    /**
     * @ORM\Column(type="boolean")
     */
    private $banned;

    /**
     * @ORM\Column(type="string", length=60)
     * @Assert\NotBlank
     * @Assert\Length(min=8, minMessage="Mot de passe trop petit")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas rentrez deux fois le même mot de passe")
     */

    private $password2;

    /**
     * @ORM\Column(type="date")
     * @Assert\NotBlank
     */
    private $dateN;

    /**
     * @ORM\Column(type="string", length=10)
     */
    private $perm;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Sagas", mappedBy="User")
     */
    private $sagas;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Series", mappedBy="User")
     */
    private $series;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Films", mappedBy="User")
     */
    private $films;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Episodes", mappedBy="User")
     */
    private $episodes;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Saisons", mappedBy="User")
     */
    private $saisons;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Commentaires", mappedBy="user")
     */
    private $commentaires;

    public function __construct()
    {
        $this->sagas = new ArrayCollection();
        $this->series = new ArrayCollection();
        $this->films = new ArrayCollection();
        $this->episodes = new ArrayCollection();
        $this->commentaires = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
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

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }
    public function getPassword2(): ?string
    {
        return $this->password2;
    }

    public function setPassword2(string $password2): self
    {
        $this->password2 = $password2;

        return $this;
    }

    public function getBanned(): ?int
    {
        return $this->banned;
    }

    public function setBanned(bool $banned): self
    {
        $this->banned = (($banned == true) ? 1 : 0);

        return $this;
    }


    public function getDateN(): ?\DateTimeInterface
    {
        return $this->dateN;
    }

    public function setDateN(\DateTimeInterface $dateN): self
    {
        $this->dateN = $dateN;

        return $this;
    }

    public function getPerm(): ?string
    {
        return $this->perm;
    }

    public function setPerm(string $perm): self
    {
        $this->perm = $perm;

        return $this;
    }

    /**
     * @return Collection|Sagas[]
     */
    public function getSagas(): Collection
    {
        return $this->sagas;
    }

    public function addSaga(Sagas $saga): self
    {
        if (!$this->sagas->contains($saga)) {
            $this->sagas[] = $saga;
            $saga->setUser($this);
        }

        return $this;
    }

    public function removeSaga(Sagas $saga): self
    {
        if ($this->sagas->contains($saga)) {
            $this->sagas->removeElement($saga);
            // set the owning side to null (unless already changed)
            if ($saga->getUser() === $this) {
                $saga->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Sagas[]
     */
    public function getSaisons(): Collection
    {
        return $this->saisons;
    }

    public function addSaison(Saisons $saison): self
    {
        if (!$this->saisons->contains($saison)) {
            $this->saisons[] = $saison;
            $saison->setUser($this);
        }

        return $this;
    }

    public function removeSaison(Saisons $saison): self
    {
        if ($this->saisons->contains($saison)) {
            $this->saisons->removeElement($saison);
            // set the owning side to null (unless already changed)
            if ($saison->getUser() === $this) {
                $saison->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Series[]
     */
    public function getSeries(): Collection
    {
        return $this->series;
    }

    public function addSeries(Series $series): self
    {
        if (!$this->series->contains($series)) {
            $this->series[] = $series;
            $series->setUser($this);
        }

        return $this;
    }

    public function removeSeries(Series $series): self
    {
        if ($this->series->contains($series)) {
            $this->series->removeElement($series);
            // set the owning side to null (unless already changed)
            if ($series->getUser() === $this) {
                $series->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Films[]
     */
    public function getFilms(): Collection
    {
        return $this->films;
    }

    public function addFilm(Films $film): self
    {
        if (!$this->films->contains($film)) {
            $this->films[] = $film;
            $film->setUser($this);
        }

        return $this;
    }

    public function removeFilm(Films $film): self
    {
        if ($this->films->contains($film)) {
            $this->films->removeElement($film);
            // set the owning side to null (unless already changed)
            if ($film->getUser() === $this) {
                $film->setUser(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Episodes[]
     */
    public function getEpisodes(): Collection
    {
        return $this->episodes;
    }

    public function addEpisode(Episodes $episode): self
    {
        if (!$this->episodes->contains($episode)) {
            $this->episodes[] = $episode;
            $episode->setUser($this);
        }

        return $this;
    }

    public function removeEpisode(Episodes $episode): self
    {
        if ($this->episodes->contains($episode)) {
            $this->episodes->removeElement($episode);
            // set the owning side to null (unless already changed)
            if ($episode->getUser() === $this) {
                $episode->setUser(null);
            }
        }

        return $this;
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    public function getSalt()
    {
        return null;
    }

    public function getRoles()
    {
        // TODO: Implement getRoles() method.
        return [$this->perm];
    }
    public function getUsername(): ?string
    {
        return $this->prenom;
    }

    public function setUsername(string $username): self
    {
        $this->prenom = $username;

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
            $commentaire->setUser($this);
        }

        return $this;
    }

    public function removeCommentaire(Commentaires $commentaire): self
    {
        if ($this->commentaires->contains($commentaire)) {
            $this->commentaires->removeElement($commentaire);
            // set the owning side to null (unless already changed)
            if ($commentaire->getUser() === $this) {
                $commentaire->setUser(null);
            }
        }

        return $this;
    }
}
