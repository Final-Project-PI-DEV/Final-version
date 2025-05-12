<?php

namespace App\Entity;
use App\Repository\LoisirRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LoisirRepository::class)]
class Loisir
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
//Controle de saisie 
    #[ORM\Column(length: 25)] // la longueur max
    #[Assert\Length(
        max: 25,
        maxMessage: 'Le nom ne doit pas dépasser {{ limit }} caractères.'
    )]
    #[Assert\Regex(
        pattern: '/^[A-Z][a-zA-Z]*$/',
        message: 'Le nom doit commencer par une majuscule et contenir uniquement des lettres.'
    )]
    private ?string $name = null;

    #[ORM\Column(type: 'text')] // Modification pour supporter un texte long
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\Type(
        type: 'integer',
        message: 'La quantité doit être un entier.'
    )]
    #[Assert\PositiveOrZero(
        message: 'La quantité ne peut pas être négative.'
    )]
    private ?int $quantity = null;

    #[ORM\Column(name: 'quantity_available')]
    #[Assert\Type(
        type: 'integer',
        message: 'La quantité disponible doit être un entier.'
    )]
    #[Assert\PositiveOrZero(
        message: 'La quantité disponible ne peut pas être négative.'
    )]
    private ?int $quantityAvailable = null;

    #[ORM\Column(type: 'text')] // Modification pour supporter un texte long
    private ?string $type = null;

    #[ORM\Column(type: 'integer')] // Modification du type en integer
    #[Assert\Type(
        type: 'integer',
        message: 'Le prix doit être un entier.'
    )]
    #[Assert\PositiveOrZero(
        message: 'Le prix ne peut pas être négatif.'
    )]
    private ?int $price = null;

    #[ORM\Column(name: 'image_path', length: 255, nullable: true)]
    private ?string $Path = null;

    #[Assert\Image(
        mimeTypes: ['image/jpeg', 'image/png'],
        maxSizeMessage: "Le fichier est trop lourd ({{ size }} {{ suffix }}). Maximum autorisé : {{ limit }} {{ suffix }}"
    )]
    private ?File $image = null;
    /**
     * 
     */

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;
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

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): static
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function getQuantityAvailable(): ?int
    {
        return $this->quantityAvailable;
    }

    public function setQuantityAvailable(int $quantityAvailable): static
    {
        $this->quantityAvailable = $quantityAvailable;
        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): static
    {
        $this->price = $price;
        return $this;
    }

    public function getImage(): ?File
    {
        return $this->image;
    }

    public function setImage(?File $image = null): void
    {
        $this->image = $image;
    }

    public function getPath(): ?string
    {
        return $this->Path;
    }
    public function setPath(?string $Path): void
    {
        $this->Path = $Path;
    }
}