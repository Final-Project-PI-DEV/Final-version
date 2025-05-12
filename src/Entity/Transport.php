<?php

namespace App\Entity;

use App\Repository\TransportRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
#[ORM\Entity(repositoryClass: TransportRepository::class)]
class Transport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Le type de transport ne peut pas être vide.")]
    private ?string $typetransport = null;
    
    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    #[Assert\NotBlank(message: 'L\'heure de départ ne peut pas être vide.')]
    #[Assert\Expression(
        expression: 'this.getHeureDepart() < this.getHeureArrive() 
            and (
                this.getHeureDepart().format("Y-m-d") == this.getEvents().getDateDebut().format("Y-m-d") 
                or 
                this.getHeureDepart().format("Y-m-d") == this.getEvents().getDateDebut().modify("-1 day").format("Y-m-d")
            )',
        message: 'L\'heure de départ doit être inférieure à l\'heure d\'arrivée et au plus 1 jour avant la date de début de l\'événement.'
    )]
    private ?\DateTimeInterface $heure_depart = null;
    
#[ORM\Column(type: Types::DATETIME_MUTABLE)]
#[Assert\NotBlank(message: 'L\'heure d\'arrivée ne peut pas être vide.')]
#[Assert\Expression(
    expression: 'this.getHeureArrive() > this.getHeureDepart() 
        and this.getHeureArrive().format("Y-m-d") == this.getEvents().getDateDebut().format("Y-m-d")',
    message: 'L\'heure d\'arrivée doit être supérieure à l\'heure de départ et le même jour que la date de début de l\'événement.'
)]
private ?\DateTimeInterface $heureArrive = null;
 
#[ORM\Column]
#[Assert\NotBlank(message: 'Le nombre d\'escales ne peut pas être vide.')]
#[Assert\Regex(
    pattern: '/^[0-5]$/',
    message: 'Le nombre d\'escales doit être compris entre 0 et 5.'
)]
private ?int $nbreescale = null;

    #[ORM\Column]
    #[Assert\NotBlank(message: 'Le nombre de place ne peut pas être vide.')]
    private ?int $nbreplace = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'Le matricule ne peut pas être vide.')]
    #[Assert\Regex(
        pattern: '/^(\d{1,3}\s)?Tunis\s\d{1,4}$|^\d{5}$/',
        message: 'Le matricule doit être au format "X Tunis XXX", "XX Tunis XX" ,"XX Tunis XX" ou "XXXXX".'
    )]
    private ?string $matricule = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: 'La description ne peut pas être vide.')]
    #[Assert\Length(min: 10, max: 100, minMessage: "La description doit comporter au moins {{ limit }} caractères.", maxMessage: "La description ne peut pas dépasser {{ limit }} caractères.")]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z\s\'\",!]+$/',
        message: 'La description ne doit contenir que des lettres, des espaces, des guillemets, des virgules et des points d\'exclamation.'
    )]
    private ?string $description = null;

    #[ORM\OneToOne(inversedBy: 'transport', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Events $events = null;

    public function getId(): ?int
    {
        return $this->id;
    }
    

    public function getTypetransport(): ?string
    {
        return $this->typetransport;
    }

    public function setTypetransport(string $typetransport): static
    {
        $this->typetransport = $typetransport;

        return $this;
    }

    public function getHeureDepart(): ?\DateTimeInterface
    {
        return $this->heure_depart;
    }

    public function setHeureDepart(\DateTimeInterface $heure_depart): static
    {
        $this->heure_depart = $heure_depart;

        return $this;
    }

    public function getHeureArrive(): ?\DateTimeInterface
    {
        return $this->heureArrive;
    }

    public function setHeureArrive(\DateTimeInterface $heureArrive): static
    {
        $this->heureArrive = $heureArrive;

        return $this;
    }

    public function getNbreescale(): ?int
    {
        return $this->nbreescale;
    }

    public function setNbreescale(int $nbreescale): static
    {
        $this->nbreescale = $nbreescale;

        return $this;
    }

    public function getNbreplace(): ?int
    {
        return $this->nbreplace;
    }

    public function setNbreplace(int $nbreplace): static
    {
        $this->nbreplace = $nbreplace;

        return $this;
    }

    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): static
    {
        $this->matricule = $matricule;

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

    public function getEvents(): ?Events
    {
        return $this->events;
    }

    public function setEvents(Events $events): static
    {
        $this->events = $events;

        return $this;
    }
}
