<?php

namespace App\Controller;

use App\Entity\Transport;
use App\Form\TransportType;
use App\Repository\TransportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/transport')]
final class TransportController extends AbstractController
{
    #[Route(name: 'app_transport_index', methods: ['GET'])]
    public function index(TransportRepository $transportRepository): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        return $this->render('transport/index.html.twig', [
            'transports' => $transportRepository->findAll(),
            'admin' => $admin,
        ]);
    }

    #[Route('/new', name: 'app_transport_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $transport = new Transport();
         
        $form = $this->createForm(TransportType::class, $transport);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            if ($form->isValid()) {
                $entityManager->persist($transport);
                $entityManager->flush();

                return $this->redirectToRoute('app_transport_index', [], Response::HTTP_SEE_OTHER);
            } else {
                // Ajoutez un dump pour voir les erreurs
                dump($form->getErrors(true, false));
            }
        }
        return $this->render('transport/new.html.twig', [
            'transport' => $transport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transport_show', methods: ['GET'])]
    public function show(Transport $transport): Response
    {
        return $this->render('transport/show.html.twig', [
            'transport' => $transport,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_transport_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Transport $transport, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $form = $this->createForm(TransportType::class, $transport);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_transport_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('transport/edit.html.twig', [
            'transport' => $transport,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_transport_delete', methods: ['POST'])]
    public function delete(Request $request, Transport $transport, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        if ($this->isCsrfTokenValid('delete' . $transport->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($transport);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_transport_index', [], Response::HTTP_SEE_OTHER);
    }
}
