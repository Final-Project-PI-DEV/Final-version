<?php

namespace App\Service;


use Symfony\Component\HttpFoundation\RequestStack;
use App\Entity\Materiel;
use App\Entity\Loisir;
use App\Entity\Events;

class PanierService
{
    private const PANIER_KEY = 'panier';

    private $session;

    public function __construct(RequestStack $requestStack)
    {
        $this->session = $requestStack->getCurrentRequest()->getSession();
    }

    public function addToPanier($item, int $quantity): void
    {
        $panier = $this->session->get(self::PANIER_KEY, []);
        
        // Détermination du type d'article
        $itemType = $this->getItemType($item);
        $itemId = $item->getId();
        
        // Récupération du prix selon l'entité
        $price = $this->getItemPrice($item);

        if (!isset($panier[$itemType][$itemId])) {
            $panier[$itemType][$itemId] = [
                'item' => $item,
                'quantity' => 0,
                'price' => $price,
            ];
        }

        $panier[$itemType][$itemId]['quantity'] += $quantity;
        $this->session->set(self::PANIER_KEY, $panier);
    }

    public function removeFromPanier($item): void
    {
        $panier = $this->session->get(self::PANIER_KEY, []);
        $itemType = $this->getItemType($item);
        $itemId = $item->getId();

        if (isset($panier[$itemType][$itemId])) {
            unset($panier[$itemType][$itemId]);
            $this->session->set(self::PANIER_KEY, $panier);
        }
    }

    public function updateQuantity($item, int $quantity): void
    {
        $panier = $this->session->get(self::PANIER_KEY, []);
        $itemType = $this->getItemType($item);
        $itemId = $item->getId();

        if (isset($panier[$itemType][$itemId])) {
            $panier[$itemType][$itemId]['quantity'] = $quantity;
            $this->session->set(self::PANIER_KEY, $panier);
        }
    }

    // Nouvelle méthode pour déterminer le type d'article
    private function getItemType($item): string
    {
        switch (true) {
            case $item instanceof Materiel:
                return 'materiel';
            case $item instanceof Loisir:
                return 'loisir';
            case $item instanceof Events:
                return 'events';
            default:
                throw new \InvalidArgumentException('Type d\'article non supporté');
        }
    }

    // Nouvelle méthode pour récupérer le prix selon l'entité
    private function getItemPrice($item): float
    {
        if ($item instanceof Events) {
            return $item->getPrix(); // Adapté pour Events
        }
        return $item->getPrice(); // Pour Materiel et Loisir
    }

    public function getPanier(): array
    {
        return $this->session->get(self::PANIER_KEY, []);
    }

    public function clearPanier(): void
    {
        $this->session->remove(self::PANIER_KEY);
    }

    public function getTotal(): float
    {
        $total = 0;
        $panier = $this->getPanier();

        foreach ($panier as $items) {
            foreach ($items as $item) {
                $total += $item['price'] * $item['quantity'];
            }
        }

        return $total;
    }
}