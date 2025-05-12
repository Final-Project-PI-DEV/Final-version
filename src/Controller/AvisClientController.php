<?php

namespace App\Controller;

use App\Entity\AvisClient;
use App\Entity\Reclamation;
use App\Repository\AvisClientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class AvisClientController extends AbstractController
{
    #[Route('/avis-client/{id}', name: 'app_avis_client')]
    public function index(Reclamation $reclamation, AvisClientRepository $avisClientRepository): Response
    {
        $hasAvis = $avisClientRepository->hasAvisForReclamation($reclamation);
        
        if ($hasAvis) {
            $this->addFlash('warning', 'Vous avez déjà donné votre avis pour cette réclamation.');
            return $this->redirectToRoute('user-liste');
        }

        return $this->render('reclamation/avis-client.html.twig', [
            'reclamation_id' => $reclamation->getId(),
        ]);
    }

    #[Route('/save-avis', name: 'app_save_avis', methods: ['POST'])]
    public function saveAvis(Request $request, EntityManagerInterface $entityManager, AvisClientRepository $avisClientRepository): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $reclamation = $entityManager->getRepository(Reclamation::class)->find($data['reclamationId']);
        if (!$reclamation) {
            return new JsonResponse(['error' => 'Réclamation non trouvée'], Response::HTTP_NOT_FOUND);
        }

        // Vérifier si un avis existe déjà
        if ($avisClientRepository->hasAvisForReclamation($reclamation)) {
            return new JsonResponse(['error' => 'Un avis existe déjà pour cette réclamation'], Response::HTTP_BAD_REQUEST);
        }

        $avisClient = new AvisClient();
        $avisClient->setReclamation($reclamation);
        $avisClient->setReponses($data['answers']);

        $entityManager->persist($avisClient);
        $entityManager->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/admin/statistiques-avis', name: 'app_admin_stats_avis')]
    public function statistiques(AvisClientRepository $avisClientRepository): Response
    {
        $stats = $avisClientRepository->getStatistiques();

        return $this->render('reclamation/statistiques-avis.html.twig', [
            'stats' => $stats
        ]);
    }
}
