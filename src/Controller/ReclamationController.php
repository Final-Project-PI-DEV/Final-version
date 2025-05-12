<?php

namespace App\Controller;

use Symfony\Component\Security\Core\Security;
use App\Entity\Reponse;
use App\Form\ReclamationType;
use App\Entity\Reclamation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use App\Entity\User;
use App\Form\UserTypeType;
use App\Service\CensorService;
use App\Entity\Message;

class ReclamationController extends AbstractController
{
    private $em;
    private $censorService;

    public function __construct(EntityManagerInterface $em, CensorService $censorService)
    {
        $this->em = $em;
        $this->censorService = $censorService;
    }

    #[Route('/create-reclamation', name: 'create-reclamation')]
    public function createreclamation(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté
        if (!$user) {
            return $this->redirectToRoute('app_login');
        };
        $reclamation = new Reclamation();
        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$reclamation->getDateReclamation()) {
                $reclamation->setDateReclamation(new \DateTime());
            }

            $reclamation->setUser($user);
            $this->em->persist($reclamation);
            $this->em->flush();
            return $this->redirectToRoute('user-liste');
        }

        return $this->render('reclamation/reclamation.html.twig', [
            'form' => $form->createView(),
            'user'=>$user,
        ]);
    }

    #[Route('/list', name: 'app_list')]
    public function listreclamation(): Response
    {
        $admin = $this->getUser();
    
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home11');
        }
        
        // Récupérer les réclamations dont le statut n'est pas "Résolue"
        $reclamations = $this->em->getRepository(Reclamation::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.reponse', 'rep')
            ->addSelect('rep')
            ->where('r.statut != :statut')
            ->setParameter('statut', 'Résolue') // Exclure les réclamations résolues
            ->getQuery()
            ->getResult();
    
        // Censurer le contenu des réclamations
        foreach ($reclamations as $reclamation) {
            $title = $reclamation->getTitle();
            $censoredTitre = $this->censorService->censorText($title);
            $reclamation->setTitle($censoredTitre);
    
            $description = $reclamation->getDescription();
            $censoredDescription = $this->censorService->censorText($description);
            $reclamation->setDescription($censoredDescription);
        }
    
        return $this->render('reclamation/list.html.twig', [
            'reclamations' => $reclamations,
            'admin' => $admin,
           
        ]);
    }

    #[Route('/change-statut/{id}', name: 'change_statut', methods: ['POST'])]
    public function changeStatut(Request $request, Reclamation $reclamation): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home11');
        }
        try {
            $statut = $request->request->get('statut');

            $statutsValides = ['En cours', 'Traité', 'En attente'];
            if (!in_array($statut, $statutsValides)) {
                throw new \InvalidArgumentException('Statut invalide');
            }

            $reclamation->setStatut($statut);
            $this->em->flush();

            $this->addFlash('success', 'Statut mis à jour avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de la mise à jour du statut : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_list');
    }

    #[Route('/repondre-reclamation/{id}', name: 'repondre_reclamation')]
    public function repondreReclamation(Request $request, Reclamation $reclamation): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home11');
        }
        try {
            $texteReponse = $request->request->get('reponse');

            if (empty(trim($texteReponse))) {
                throw new \InvalidArgumentException('La réponse ne peut pas être vide');
            }

            // Si une réponse existe déjà, mettre à jour sa date et son texte
            if ($reclamation->getReponse()) {
                $reponse = $reclamation->getReponse();
                $reponse->setTextReponse($texteReponse);
                $reponse->setDateReponse(new \DateTime());
            } else {
                // Sinon créer une nouvelle réponse
                $reponse = new Reponse();
                $reponse->setTextReponse($texteReponse);
                $reponse->setDateReponse(new \DateTime());
                $reponse->setIsFinal(true);
                $reponse->setReclamation($reclamation);
            }

            $reclamation->setStatut('Traité');

            $this->em->persist($reponse);
            $this->em->flush();

            $this->addFlash('success', 'Réponse envoyée avec succès !');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur lors de l\'envoi de la réponse : ' . $e->getMessage());
        }

        return $this->redirectToRoute('app_list');
    }

    #[Route('/delete-reclamation/{id}', name: 'delete_reclamation')]
    public function deletereclamation(Reclamation $reclamation): Response
    {
        // Supprimer les messages associés à la réclamation
        $messages = $this->em->getRepository(Message::class)->findBy(['reclamation' => $reclamation]);
        foreach ($messages as $message) {
            $this->em->remove($message);
        }

        // Supprimer la réclamation
        $this->em->remove($reclamation);
        $this->em->flush();

        $this->addFlash('success', 'Réclamation supprimée avec succès !');
        return $this->redirectToRoute('app_list');
    }

    #[Route('/archive', name: 'app_archive')]
    public function archive(Request $request): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home11');
        }
    
        // Récupérer la date de recherche depuis l'URL
        $searchDate = $request->query->get('searchDate');
    
        // Construire la requête pour récupérer les réclamations résolues
        $queryBuilder = $this->em->getRepository(Reclamation::class)
            ->createQueryBuilder('r')
            ->leftJoin('r.reponse', 'rep')
            ->addSelect('rep')
            ->where('r.statut = :statut')
            ->setParameter('statut', 'Résolue');
    
        // Filtrer par date si une date est fournie
        if ($searchDate) {
            $queryBuilder->andWhere('r.dateReclamation LIKE :searchDate')
                ->setParameter('searchDate', '%' . $searchDate . '%');
        }
    
        // Exécuter la requête
        $reclamations = $queryBuilder->getQuery()->getResult();
    
        // Censurer le contenu des réclamations
        foreach ($reclamations as $reclamation) {
            $title = $reclamation->getTitle();
            $censoredTitre = $this->censorService->censorText($title);
            $reclamation->setTitle($censoredTitre);
    
            $description = $reclamation->getDescription();
            $censoredDescription = $this->censorService->censorText($description);
            $reclamation->setDescription($censoredDescription);
        }
    
        return $this->render('reclamation/archive.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/userlist', name: 'user-liste')]
    public function listeClient(): Response
    {
        // Récupérer directement l'utilisateur avec ID 1
        $user = $this->getUser();;

        if (!$user) {
            throw $this->createNotFoundException('Utilisateur avec ID 1 non trouvé');
        }

        // Récupérer toutes les réclamations de l'utilisateur avec ID 1
        $reclamations = $this->em->getRepository(Reclamation::class)
            ->findBy(['user' => $user]);

        return $this->render('reclamation/reclamationuser.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }

    #[Route('/edit-reclamation/{id}', name: 'edit_reclamation')]
    public function editReclamation(Request $request, Reclamation $reclamation): Response
    {
        $user = $this->getUser();
        
        // Vérifier si l'utilisateur est le propriétaire de la réclamation
        if ($reclamation->getUser() !== $user) {
            $this->addFlash('error', 'Vous n\'êtes pas autorisé à modifier cette réclamation');
            return $this->redirectToRoute('user-liste');
        }

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Mettre à jour la date automatiquement
            $reclamation->setDateReclamation(new \DateTime());
            
            $this->em->flush();
            $this->addFlash('success', 'Réclamation modifiée avec succès !');
            return $this->redirectToRoute('user-liste');
        }

        return $this->render('reclamation/reclamation.html.twig', [
            'form' => $form->createView(),
            'reclamation' => $reclamation
        ]);
    }

    #[Route('/conversation/{id}', name: 'conversation')]
    public function conversation(Request $request, Reclamation $reclamation): Response
    {
        // Vérifiez que l'utilisateur a le droit d'accéder à la conversation
        if (!$this->isGranted('ROLE_USER') && !$this->isGranted('ROLE_ADMIN')) {
            return $this->redirectToRoute('home11');
        }

        // Vérifiez que la réclamation est en statut "en attente" pour l'utilisateur
        if ($this->isGranted('ROLE_USER') && $reclamation->getStatut() !== 'en attente') {
            return $this->redirectToRoute('home11');
        }

        // Récupérer les messages associés à la réclamation
        $messages = $this->em->getRepository(Message::class)->findBy(['reclamation' => $reclamation]);

        // Gérer l'envoi d'un nouveau message
        $messageContent = $request->request->get('message');
        if ($messageContent) {
            $message = new Message();
            $message->setContent($messageContent);
            $message->setReclamation($reclamation);
            $message->setUser($this->getUser()); // Associer le message à l'utilisateur connecté
            $this->em->persist($message);
            $this->em->flush();

            // Rediriger pour éviter le re-soumission du formulaire
            return $this->redirectToRoute('conversation', ['id' => $reclamation->getId()]);
        }

        return $this->render('reclamation/conversation.html.twig', [
            'reclamation' => $reclamation,
            'messages' => $messages,
        ]);
    }
}
