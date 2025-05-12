<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\Reclamation;
use App\Entity\Reponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use App\Form\ReponseType;
use App\Form\ReclamationType;
final class ReponseController extends AbstractController
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/reponse', name: 'app_reponse')]
    public function index(): Response
    {
        return $this->render('reponse/index.html.twig', [
            'controller_name' => 'ReponseController',
        ]);
    }

    #[Route('/create-reponse/{id}', name: 'create-reponse')]
    public function createreponse(Request $request, $id): Response
    {   $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        // Récupérer la réclamation
        $reclamation = $this->em->getRepository(Reclamation::class)->find($id);
        
        if (!$reclamation) {
            throw $this->createNotFoundException('Réclamation non trouvée');
        }

        // Créer ou récupérer la réponse
        $reponse = $reclamation->getReponse() ?? new Reponse();
        if (!$reclamation->getReponse()) {
            $reponse->setReclamation($reclamation);
        }

        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($reponse);
            $this->em->flush();
            
            $this->addFlash('message', '✅ Réponse ajoutée avec succès !');
            return $this->redirectToRoute('app_list');
        }

        return $this->render('reclamation/reponse.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation
        ]);
    }



}