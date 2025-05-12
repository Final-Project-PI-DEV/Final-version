<?php

namespace App\Entity;

use App\Repository\RestaurantRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


#[ORM\Entity(repositoryClass: RestaurantRepository::class)]
class Restaurant
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank(message: "Le nom ne peut pas être vide.")]
    private ?string $nom_resto = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank(message: "La specialite ne peut pas être vide.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-ZÀ-ÖØ-öø-ÿ\s'-]+$/",
        message: "La spécialité ne doit contenir que des lettres."
    )]
    private ?string $specialite = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La description ne peut pas être vide.")]
    private ?string $description = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank(message: "L'email ne peut pas être vide.")]
    #[Assert\Email(message: "L'adresse email '{{ value }}' n'est pas valide.")]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\Type(type: "\DateTimeInterface", message: "La valeur '{{ value }}' n'est pas une date valide.")]
    private ?\DateTimeInterface $date_debut_colab = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\Type(type: "\DateTimeInterface", message: "La valeur '{{ value }}' n'est pas une date valide.")]
    private ?\DateTimeInterface $date_fin_colab = null;


    #[ORM\Column(length: 255, nullable: true)]
    //private ?string $statut = null;
    private ?string $statut = 'actif';



    #[ORM\Column(length: 255, nullable: true)]
    private ?string $image = null;

    /**
     * @var Collection<int, Menu>
     */
    #[ORM\OneToMany(targetEntity: Menu::class, mappedBy: 'restaurant')]
    private Collection $Menu;

    #[ORM\Column(length: 255)]
    private ?string $adresse = null;

    #[ORM\Column]
    private ?float $averageRating = null;

    #[ORM\Column]
    private ?int $ratingCount = null;





    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addConstraint(new Assert\Callback('validateDates'));
    }

    public function validateDates(ExecutionContextInterface $context): void
    {
        if ($this->date_debut_colab && $this->date_fin_colab) {
            if ($this->date_debut_colab > $this->date_fin_colab) {
                $context->buildViolation("La date de début doit être antérieure à la date de fin.")
                    ->atPath("date_debut_colab")
                    ->addViolation();
            }
        }
    }




    public function __construct()
    {
        $this->Menu = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNomResto(): ?string
    {
        return $this->nom_resto;
    }

    public function setNomResto(string $nom_resto): static
    {
        $this->nom_resto = $nom_resto;

        return $this;
    }

    public function getSpecialite(): ?string
    {
        return $this->specialite;
    }

    public function setSpecialite(string $specialite): static
    {
        $this->specialite = $specialite;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getDateDebutColab(): ?\DateTimeInterface
    {
        return $this->date_debut_colab;
    }

    public function setDateDebutColab(\DateTimeInterface $date_debut_colab): static
    {
        $this->date_debut_colab = $date_debut_colab;

        return $this;
    }

    public function getDateFinColab(): ?\DateTimeInterface
    {
        return $this->date_fin_colab;
    }

    public function setDateFinColab(\DateTimeInterface $date_fin_colab): static
    {
        $this->date_fin_colab = $date_fin_colab;

        return $this;
    }

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): static
    {
        $this->statut = $statut;

        return $this;
    }

    public function updateStatus(): void
    {
        if ($this->date_fin_colab instanceof \DateTimeInterface && $this->date_fin_colab < new \DateTime()) {
            $this->statut = 'inactif';
        }
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }

    /**
     * @return Collection<int, Menu>
     */
    public function getMenu(): Collection
    {
        return $this->Menu;
    }

    public function addMenu(Menu $menu): static
    {
        if (!$this->Menu->contains($menu)) {
            $this->Menu->add($menu);
            $menu->setRestaurant($this);
        }

        return $this;
    }

    public function removeMenu(Menu $menu): static
    {
        if ($this->Menu->removeElement($menu)) {
            // set the owning side to null (unless already changed)
            if ($menu->getRestaurant() === $this) {
                $menu->setRestaurant(null);
            }
        }

        return $this;
    }





    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): static
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getAverageRating(): ?float
    {
        return $this->averageRating;
    }

    public function setAverageRating(float $averageRating): static
    {
        $this->averageRating = $averageRating;

        return $this;
    }

    public function getRatingCount(): ?int
    {
        return $this->ratingCount;
    }

    public function setRatingCount(int $ratingCount): static
    {
        $this->ratingCount = $ratingCount;

        return $this;
    }

    public function updateAverageRating(float $newRating): void
    {
        if ($this->averageRating === null) {
            $this->averageRating = $newRating;
            $this->ratingCount = 1;
        } else {
            $totalRating = $this->averageRating * $this->ratingCount;
            $totalRating += $newRating;
            $this->ratingCount++;
            $this->averageRating = $totalRating / $this->ratingCount;
        }
    }
}
