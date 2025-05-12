<?php

namespace App\Controller;


use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Dompdf\Dompdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


final class UserController extends AbstractController
{
    #[Route('/admin', name: 'app_user_index', methods: ['GET'])]
    public function index(UserRepository $userRepository): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        return $this->render('user/index.html.twig', [
            'users' => $userRepository->findAll(),
            'admin' => $admin,
        ]);
    }

    #[Route('user/new', name: 'app_user_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher, MailerInterface $mailer): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setDateDeNaissance($form->get('date_de_naissance')->getData());
            $user->setRoles(['ROLE_USER']);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            // Gestion de l'upload d'image 
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'), // Assurez-vous que ce paramètre est configuré
                        $newFilename
                    );
                    $user->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();
            try {
                $email = (new TemplatedEmail())
                    ->from('ooyosri@gmail.com') // Remplacez par votre email
                    ->to($user->getEmail())
                    ->subject('Bienvenue sur notre plateforme !')
                    ->htmlTemplate('user/welcome.html.twig')
                    ->context([
                        'user' => $user,
                        'date_inscription' => new \DateTime()
                    ]);

                $mailer->send($email);
            } catch (TransportExceptionInterface $e) {
                // Gérer l'erreur d'envoi (log ou notification)
                $this->addFlash('warning', 'L\'email de bienvenue n\'a pas pu être envoyé.');
            }

            return $this->redirectToRoute('home11', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('admin/new_admin', name: 'app_admin_new', methods: ['GET', 'POST'])]
    public function newadmin(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger, UserPasswordHasherInterface $passwordHasher): Response
    {
        $admin = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user->setDateDeNaissance($form->get('date_de_naissance')->getData());
            $user->setRoles(['ROLE_ADMIN']);
            $hashedPassword = $passwordHasher->hashPassword(
                $user,
                $user->getPassword()
            );
            $user->setPassword($hashedPassword);
            // Gestion de l'upload d'image
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'), // Assurez-vous que ce paramètre est configuré
                        $newFilename
                    );
                    $user->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $entityManager->persist($user);
            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/user/home11', name: 'home11')]
    public function index7(): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/home.html.twig', [
            'user' => $user // Passer l'utilisateur à Twig
        ]);
    }


    #[Route('/{id}/edit', name: 'app_user_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $admin = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Gestion de l'upload d'image (mise à jour)
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('uploads_directory'),
                        $newFilename
                    );
                    $user->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image.');
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_user_delete', methods: ['POST'])]
    public function delete(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
    #[Route('/admin/user/{id}/deactivate', name: 'admin_user_deactivate', methods: ['GET', 'POST'])]
    public function deactivate(
        Request $request,
        User $user,
        EntityManagerInterface $em
    ): Response {
        // Vérification des permissions
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        // Validation CSRF
        if ($this->isCsrfTokenValid('deactivate' . $user->getId(), $request->request->get('_token'))) {
            // Désactiver l'utilisateur
            $user->setIsActive(false);
            $em->flush();

            // Déconnecter l'utilisateur si c'est le compte actuel


            $this->addFlash('success', 'Utilisateur désactivé avec succès');
        } else {
            $this->addFlash('error', 'Token CSRF invalide');
        }

        return $this->redirectToRoute('app_user_index');
    }

    #[Route('/admin/user/activate/{id}', name: 'admin_user_activate', methods: ['GET', 'POST'])]
    public function activate(User $user, EntityManagerInterface $entityManager, Request $request): Response
    {
        if ($this->isCsrfTokenValid('activate' . $user->getId(), $request->request->get('_token'))) {
            $user->setIsActive(true); // Active l'utilisateur
            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Utilisateur activé avec succès.');
        }

        return $this->redirectToRoute('app_user_index');
    }
    #[Route('/admin/user/{id}/export-pdf', name: 'admin_user_export_pdf')]
    public function exportPdf(int $id, EntityManagerInterface $entityManager): Response
    {
        // Récupérer l'utilisateur
        $user = $entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw new NotFoundHttpException("Utilisateur non trouvé.");
        }

        // Créer une instance de TCPDF
        $pdf = new \TCPDF();

        // Paramètres du document
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Votre Application');
        $pdf->SetTitle('Fiche Utilisateur');
        $pdf->SetSubject('Détails de l\'utilisateur');

        // Supprimer les headers et footers par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        // Ajouter le titre
        $pdf->SetFillColor(0, 102, 204); // Bleu foncé
        $pdf->SetTextColor(255, 255, 255); // Blanc
        $pdf->SetFont('helvetica', 'B', 18);

        // Titre avec un fond coloré et un espacement
        $pdf->Cell(0, 15, 'Badge Camper', 0, 1, 'C', true);
        $pdf->Ln(5);

        // Affichage de l'image (si disponible)
        if ($user->getImage()) {
            $imagePath = $this->getParameter('kernel.project_dir') . '/public/uploads/' . $user->getImage();
            if (file_exists($imagePath)) {
                $pdf->Image($imagePath, 75, 40, 60, 60, '', '', '', true, 300, '', false, false, 1, false, false, false);

                // Déplacer le curseur plus bas pour éviter l'overlap avec le tableau
                $pdf->Ln(80);
            }
        }

        // Définition des couleurs
        $pdf->SetFillColor(200, 220, 255); // Bleu clair pour les titres du tableau
        $pdf->SetTextColor(0, 0, 128); // Bleu foncé pour le texte des coordonnées
        $pdf->SetFont('helvetica', '', 12);

        // Tableau avec bordures et couleurs
        $pdf->Cell(60, 10, 'Nom', 1, 0, 'C', true);
        $pdf->Cell(130, 10, $user->getNom(), 1, 1, 'C', false);

        $pdf->Cell(60, 10, 'Prénom', 1, 0, 'C', true);
        $pdf->Cell(130, 10, $user->getPrenom(), 1, 1, 'C', false);

        $pdf->Cell(60, 10, 'Email', 1, 0, 'C', true);
        $pdf->Cell(130, 10, $user->getEmail(), 1, 1, 'C', false);

        $pdf->Cell(60, 10, 'Téléphone', 1, 0, 'C', true);
        $pdf->Cell(130, 10, $user->getTelephone(), 1, 1, 'C', false);

        $pdf->Cell(60, 10, 'Date de naissance', 1, 0, 'C', true);
        $pdf->Cell(130, 10, $user->getDateDeNaissance()->format('d/m/Y'), 1, 1, 'C', false);

        // Footer avec la date d'exportation
        $pdf->Ln(10);
        $pdf->SetTextColor(128, 128, 128); // Gris pour le footer
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'Exporté le : ' . date('d/m/Y H:i'), 0, 1, 'C');

        // Générer et retourner le PDF
        return new Response(
            $pdf->Output('Fiche_' . $user->getNom() . '.pdf', 'D'),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="Fiche_' . $user->getNom() . '.pdf"'
            ]
        );
    }
}
