<?php

namespace App\Entity;

use App\Repository\EventsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EventsRepository::class)]
class Events
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le titre ne peut pas être vide.')]
    #[Assert\Length(min: 5, max: 13, minMessage: "Le titre doit comporter au moins {{ limit }} caractères.", maxMessage: "La description ne peut pas dépasser {{ limit }} caractères.")]
    private ?string $titre = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide.')]
    #[Assert\Length(min: 10, max: 100, minMessage: "La description doit comporter au moins {{ limit }} caractères.", maxMessage: "La description ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s\'\",!]+$/',
        message: 'La description ne doit contenir que des lettres, des espaces, des guillemets, des virgules et des points d\'exclamation.'
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le lieu ne peut pas être vide.')]
    #[Assert\Length(min: 4, max: 10, minMessage: "Le lieu doit comporter au moins {{ limit }} caractères.", maxMessage: "La description ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s]+$/',
        message: 'Le lieu ne doit contenir que des lettres, des espaces, des guillemets, des virgules et des points d\'exclamation.'
    )]
    private ?string $lieu = null;


    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de début est requise.")]
    #[Assert\GreaterThan(
        value: "now + 5 days",
        message: "La date de début doit être supérieure de 5 jours à la date actuelle."
    )]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    #[Assert\NotBlank(message: "La date de fin est requise.")]
    #[Assert\Expression(
        "this.getDateDebut() && value >= this.getDateDebut().modify('+2 days')",
        message: "La date de fin doit être au moins 2 jours après la date de début."
    )]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: "Le prix ne peut pas être vide.")]
    #[Assert\GreaterThan(value: 0, message: "Le prix doit être un nombre positif.")]
    #[Assert\Regex(
        pattern: "/^\d{1,4}(\.\d{1,3})?$/",
        message: "Le prix doit être un nombre avec jusqu'à 4 chiffres avant la virgule et 3 chiffres après la virgule."
    )]
    private ?float $prix = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le film ne peut pas être vide.")]
    #[Assert\Length(min: 4, max: 15, minMessage: "Le film doit comporter au moins {{ limit }} caractères.", maxMessage: "La description ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: "/^[a-zA-Z0-9\s]+$/",
        message: "Le film doit contenir uniquement des lettres, des chiffres et des espaces."
    )]
    private ?string $film = null;

    #[ORM\Column(length: 255)]

    private ?string $image = null;
    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type d'événement ne peut pas être vide.")]
    private ?string $typeevent = null;

    #[ORM\OneToOne(mappedBy: 'events', cascade: ['persist', 'remove'])]
    private ?Transport $transport = null;
    //
    #[ORM\ManyToOne(inversedBy: 'events')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $User = null;




    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): static
    {
        $this->titre = $titre;

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

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): static
    {
        $this->lieu = $lieu;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getFilm(): ?string
    {
        return $this->film;
    }

    public function setFilm(string $film): static
    {
        $this->film = $film;

        return $this;
    }

    public function getTypeevent(): ?string
    {
        return $this->typeevent;
    }

    public function setTypeevent(string $typeevent): static
    {
        $this->typeevent = $typeevent;

        return $this;
    }

    public function getTransport(): ?Transport
    {
        return $this->transport;
    }

    public function setTransport(Transport $transport): static
    {
        // set the owning side of the relation if necessary
        if ($transport->getEvents() !== $this) {
            $transport->setEvents($this);
        }

        $this->transport = $transport;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(?User $User): static
    {
        $this->User = $User;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): static
    {
        $this->image = $image;

        return $this;
    }
}
