<?php

namespace App\Controller;

use App\Entity\Materiel;
use App\Entity\Loisir;
use App\Entity\Events;
use App\Service\PanierService;
use Doctrine\ORM\EntityManagerInterface; 
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PanierController extends AbstractController
{
    private PanierService $panierService;
    private EntityManagerInterface $entityManager; 

    public function __construct(PanierService $panierService, EntityManagerInterface $entityManager) 
    {
        $this->panierService = $panierService;
        $this->entityManager = $entityManager; 
    }

    #[Route('/ajouter/{type}/{id}', name: 'ajouter_au_panier')]
    public function ajouterAuPanier($type, $id, Request $request): Response
    {
        $item = match($type) {
            'materiel' => $this->entityManager->getRepository(Materiel::class)->find($id),
            'loisir' => $this->entityManager->getRepository(Loisir::class)->find($id),
            'events' => $this->entityManager->getRepository(Events::class)->find($id),
            default => null
        };
    
        if ($item) {
            $quantity = $request->query->getInt('quantity', 1);
            $this->panierService->addToPanier($item, $quantity);
            return $this->redirectToRoute('panier');
        }
    
        throw $this->createNotFoundException('Item non trouvé');
    }
    #[Route('/panier', name: 'panier')]
    public function afficherPanier(): Response
    {
        $panier = $this->panierService->getPanier();
        $total = $this->panierService->getTotal();

        return $this->render('panier/index.html.twig', [
            'panier' => $panier,
            'total' => $total,
        ]);
    }

    #[Route('/supprimer/{type}/{id}', name: 'supprimer_du_panier')]
    public function supprimerDuPanier($type, $id): Response
    {
        $item = match($type) {
            'materiel' => $this->entityManager->getRepository(Materiel::class)->find($id),
            'loisir' => $this->entityManager->getRepository(Loisir::class)->find($id),
            'events' => $this->entityManager->getRepository(Events::class)->find($id),
            default => null
        };
    
        if ($item) {
            $this->panierService->removeFromPanier($item);
            return $this->redirectToRoute('panier');
        }
    
        throw $this->createNotFoundException('Item non trouvé');
    }
}