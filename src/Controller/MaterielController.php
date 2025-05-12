<?php

namespace App\Controller;
use App\Entity\Materiel;
use App\Form\MaterielType;
use App\Repository\MaterielRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
#[Route('/materiel')]
final class MaterielController extends AbstractController
{
    #[Route('/details/{id}', name: 'app_materiel_details', methods: ['GET'])]
    public function detailsmateriel(Materiel $materiel): Response
    {
        return $this->render('materiel/details.html.twig', [
            'materiel' => $materiel,
        ]);
    }
    #[Route(name: 'app_materiel_index', methods: ['GET'])]
    public function index(MaterielRepository $materielRepository): Response
    {
        return $this->render('materiel/index.html.twig', [
            'materiels' => $materielRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_materiel_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $materiel = new Materiel();
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();
    
            if ($file) {
                $filename = uniqid() . '.' . $file->guessExtension();
                $file->move($this->getParameter('images_directory'), $filename);
                $materiel->setImagePath($filename);
            }
    
            $entityManager->persist($materiel);
            $entityManager->flush();
    
            // Message de succès
            $this->addFlash('success', 'Le matériel a été créé avec succès!');
            return $this->redirectToRoute('app_materiel_index');
        }
    
        return $this->render('materiel/new.html.twig', [
            'form' => $form->createView(),
            'admin' => $admin,
        ]);
    }

    #[Route('/{id}', name: 'app_materiel_show', methods: ['GET'])]
    public function show(Materiel $materiel): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        return $this->render('materiel/show.html.twig', [
            'materiel' => $materiel,
            'admin' => $admin,
        ]);
    }

    
    #[Route('/{id}/edit', name: 'app_materiel_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Materiel $materiel, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $form = $this->createForm(MaterielType::class, $materiel);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $file */
            $file = $form->get('image')->getData();
    
            if ($file) {
                // Supprimer l'ancienne image
                if ($materiel->getImagePath()) {
                    $oldPath = $this->getParameter('images_directory').'/'.$materiel->getImagePath();
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
    
                $filename = uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('images_directory'), $filename);
                $materiel->setImagePath($filename);
            }
    
            $entityManager->flush();
    
            return $this->redirectToRoute('app_materiel_index');
        }
    
        return $this->render('materiel/edit.html.twig', [
            'materiel' => $materiel, 
            'form' => $form->createView(),
            'admin' => $admin,  // Ajout de l'admin pour vérifier si l'utilisateur est administrateur
        ]);
    }

    #[Route('/{id}', name: 'app_materiel_delete', methods: ['POST'])]
    public function delete(Request $request, Materiel $materiel, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$materiel->getId(), $request->request->get('_token'))) {
            // Supprimer l'image associée
            $filename = $materiel->getImagePath();
            if ($filename) {
                $filePath = $this->getParameter('images_directory').'/'.$filename;
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            $entityManager->remove($materiel);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_materiel_index', [], Response::HTTP_SEE_OTHER); // Correction de la typo
    }
//     #[Route(name: 'app_materiel_index', methods: ['GET'])]
// public function index1(MaterielRepository $materielRepository, Request $request): Response
// {
//     $searchTerm = $request->query->get('q');
    
//     $materiels = $searchTerm 
//     $materielRepository->searchByName($searchTerm) : $materielRepository->findAll();

//     return $this->render('materiel/index.html.twig', [
//         'materiels' => $materiels,
//         'search_term' => $searchTerm
//     ]);
// }
// #[Route('/client', name: 'app_materiel_client', methods: ['GET'])]
// public function index(Request $request, PaginatorInterface $paginator, MaterielRepository $materielRepository): Response
// {
//     $query = $materielRepository->createQueryBuilder('m')
//         ->getQuery();
    
//     $pagination = $paginator->paginate(
//         $query,
//         $request->query->getInt('page', 1),
//         6
//     );
    
//     return $this->render('materiel/client.html.twig', [
//         'materiels' => $pagination
//     ]);
// }

}