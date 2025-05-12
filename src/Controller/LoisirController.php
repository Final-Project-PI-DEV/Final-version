<?php

namespace App\Controller;
use App\Entity\Loisir;

use App\Form\LoisirType;
use App\Repository\LoisirRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\OpenAIService;

#[Route('/loisir')]
final class LoisirController extends AbstractController
{
    #[Route('/details/{id}', name: 'app_loisir_details', methods: ['GET'])]
    public function detailsloisir(Loisir $loisir): Response
    {
        return $this->render('loisir/details.html.twig', [
            'loisir' => $loisir,
        ]);
    }
    #[Route(name: 'app_loisir_index', methods: ['GET'])]
    public function index(LoisirRepository $loisirRepository): Response
    {
        return $this->render('loisir/index.html.twig', [
            'loisirs' => $loisirRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_loisir_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $loisir = new Loisir();
        $form = $this->createForm(LoisirType::class, $loisir);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
              /** @var UploadedFile $file */
            $file = $form->get('image')->getData();
            if ($file) {
                $filename = uniqid().'.'.$file->guessExtension();
                $file->move(
                    $this->getParameter('images_directory'), 
                    $filename
                );
                $loisir->setPath($filename); 
            }
            $entityManager->persist($loisir);
            $entityManager->flush();

            return $this->redirectToRoute('app_loisir_index');
        }

        return $this->render('loisir/new.html.twig', [
            'form' => $form->createView(),
            'admin' => $admin,
        ]);
    
    }

    #[Route('/{id}', name: 'app_loisir_show', methods: ['GET'])]
    public function show(Loisir $loisir): Response
    {
        return $this->render('loisir/show.html.twig', [
            'loisir' => $loisir,
            
        ]);
    }

    #[Route('/{id}/edit', name: 'app_loisir_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Loisir $loisir, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $form = $this->createForm(LoisirType::class, $loisir);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();
    
            if ($file) {
                // Supprimer l'ancienne image
                if ($loisir->getPath()) {
                    $oldPath = $this->getParameter('images_directory').'/'.$loisir->getPath();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
    
                $filename = uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('images_directory'), $filename);
                $loisir->setPath($filename);
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_loisir_index');
        }
    
        return $this->render('loisir/edit.html.twig', [
            'loisir' => $loisir, 
            'form' => $form->createView(),
            'admin' => $admin,  // Ajout d'un admin pour le formulaire d'édition
        ]);
    }

    #[Route('/{id}', name: 'app_loisir_delete', methods: ['POST'])]
    public function delete(Request $request, Loisir $loisir, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$loisir->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée
            $filename = $loisir->getPath();
            if ($filename) {
                $filePath = $this->getParameter('images_directory').'/'.$filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $entityManager->remove($loisir);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_loisir_index', [], Response::HTTP_SEE_OTHER); 
    }
    #[Route('/generate-description', name: 'loisir_generate_description', methods: ['POST'], defaults: ['_csrf_token' => false])]
public function generateDescription(Request $request, OpenAIService $openAIService): Response
{
    $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
    
    $data = json_decode($request->getContent(), true);
    
    if (empty($data['item_name'])) {
        return $this->json(['error' => 'Le nom est obligatoire'], 400);
    }

    try {
        $description = $openAIService->generateDescription(
            'équipement', // Type fixe pour les loisirs
            $data['item_name'],
            $data['characteristics'] ?? []
        );

        return $this->json(['description' => $description]);
        
    } catch (\Exception $e) {
        return $this->json([
            'error' => 'Erreur de génération : ' . $e->getMessage()
        ], 500);
}
}
}
