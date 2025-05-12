<?php

namespace App\Entity;

use App\Repository\ReponseRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReponseRepository::class)]
class Reponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "La Réponse ne peut pas être vide.")]
    #[Assert\Length(min: 6, max: 255, minMessage: "La Réponse doit comporter au moins 6 caractères.", maxMessage: "La Réponse ne peut pas dépasser 255 caractères.")]
    private ?string $text_reponse = null;

    #[ORM\Column]
    private ?bool $is_final = true;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date_reponse = null;

    #[ORM\OneToOne(inversedBy: 'reponse', cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?Reclamation $reclamation = null;

    public function __construct()
    {
        $this->date_reponse = new \DateTime();
        $this->is_final = true; // Par défaut à true
        
        // Si une réclamation est associée, mettre directement son statut à "résolue"
        if ($this->reclamation !== null) {
            $this->reclamation->setStatut('résolue');
        }
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTextReponse(): ?string
    {
        return $this->text_reponse;
    }

    public function setTextReponse(string $text_reponse): static
    {
        $this->text_reponse = $text_reponse;
        $this->date_reponse = new \DateTime(); // Met à jour la date à chaque modification de la réponse
        return $this;
    }

    public function isIsFinal(): ?bool
    {
        return $this->is_final;
    }

    public function setIsFinal(bool $is_final): static
    {
        $this->is_final = $is_final;

        // Mise à jour immédiate du statut de la réclamation
        if ($this->reclamation !== null) {
            if ($is_final) {
                $this->reclamation->setStatut('résolue');
            } else {
                $this->reclamation->setStatut('en attente');
            }
        }

        return $this;
    }


    public function getDateReponse(): ?\DateTimeInterface
    {
        return $this->date_reponse;
    }

    public function setDateReponse(\DateTimeInterface $date_reponse): static
    {
        $this->date_reponse = $date_reponse;
        return $this;
    }

    public function getReclamation(): ?Reclamation
    {
        return $this->reclamation;
    }

    public function setReclamation(Reclamation $reclamation): static
    {
        $this->reclamation = $reclamation;
        
        // Mise à jour immédiate du statut lors de l'association
        if ($this->is_final) {
            $reclamation->setStatut('résolue');
        } else {
            $reclamation->setStatut('en attente');
        }
        
        return $this;
    }

    // Ajout d'un alias pour la compatibilité avec le template
    public function getReponse(): ?string
    {
        return $this->text_reponse;
    }
    
}