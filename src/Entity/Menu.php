<?php

namespace App\Entity;

use App\Repository\MenuRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: MenuRepository::class)]
class Menu
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 55)]
    #[Assert\NotBlank(message: "Le repas ne peut pas être vide.")]
    private ?string $repas = null;

    #[ORM\Column(length: 255)]
    private ?string $prix = null;

    /* #[ORM\ManyToOne(inversedBy: 'Menu')]*/
    #[ORM\ManyToOne(targetEntity: Restaurant::class, inversedBy: 'Menu')]

    private ?Restaurant $restaurant = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRepas(): ?string
    {
        return $this->repas;
    }

    public function setRepas(string $repas): static
    {
        $this->repas = $repas;

        return $this;
    }

    public function getPrix(): ?string
    {
        return $this->prix;
    }

    public function setPrix(string $prix): static
    {
        $this->prix = $prix;

        return $this;
    }

    public function getRestaurant(): ?Restaurant
    {
        return $this->restaurant;
    }

    public function setRestaurant(?Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;

        return $this;
    }
}
