<?php

namespace App\Controller;

use App\Entity\Events; // Changed from Aziz to Events
use App\Form\EventsType; // Changed from AzizType to EventsType
use App\Repository\EventsRepository; // Changed from AzizRepository to EventsRepository
use App\Repository\TransportRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
#[Route('/events')]
final class EventsController extends AbstractController
{
    #[Route(name: 'app_events_index', methods: ['GET'])]
    public function index(EventsRepository $eventsRepository): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Rediriger l'utilisateur vers la page d'accueil si ce n'est pas un admin
            return $this->redirectToRoute('home11');
        }

        // Récupérer tous les événements
        $events = $eventsRepository->findAll();
    
        // Calculer le nombre d'événements par type
        $eventTypesCount = [];
        foreach ($events as $event) {
            $type = $event->getTypeevent();
            if (!isset($eventTypesCount[$type])) {
                $eventTypesCount[$type] = 0;
            }
            $eventTypesCount[$type]++;
        }

        // Préparer les données pour le graphique circulaire
        $eventTypes = array_keys($eventTypesCount);
        $eventCounts = array_values($eventTypesCount);

        // Initialiser un tableau pour compter les événements par mois
        $monthlyEventCounts = array_fill(0, 12, 0); // 12 mois

        // Parcourir les événements et compter ceux de chaque mois
        foreach ($events as $event) {
            $dateDebut = $event->getDateDebut();
            if ($dateDebut) {
                $month = (int) $dateDebut->format('m') - 1; // Les indices de tableau commencent à 0
                $monthlyEventCounts[$month]++;
            }
        }

        // Préparer les données pour le graphique linéaire
        $months = [
            'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
            'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
        ];

        return $this->render('events/index.html.twig', [
            'events' => $events,
            'admin' => $admin,
            'eventTypes' => $eventTypes,
            'eventCounts' => $eventCounts,
            'monthlyEventCounts' => $monthlyEventCounts,
            'months' => $months,
        ]);
    }




    #[Route('/new', name: 'app_events_new', methods: ['GET', 'POST'])]
    public function new(Request $request, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $event = new Events();
        $event->setUser($admin);
        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            // If image is required, make sure it's checked properly
            if ($imageFile) {
                // Validate MIME type
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($imageFile->getMimeType(), $allowedMimeTypes)) {
                    $this->addFlash('error', 'Type de fichier non supporté');
                    return $this->render('events/new.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                // File upload handling
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $event->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l\'upload');
                    return $this->redirectToRoute('app_events_index');
                }
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Événement créé avec succès');
            return $this->redirectToRoute('app_events_index');
        }

        return $this->render('events/new.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('events/{id}', name: 'app_events_show', methods: ['GET'])]
    public function show(Events $events): Response // Updated method parameter
    {
        return $this->render('events/eventshow.html.twig', [ // Updated template path
            'events' => $events, // Updated variable name
        ]);
    }
    #[Route('dashshow/{id}', name: 'app_dash_show', methods: ['GET'])]
    public function show1(Events $events): Response // Updated method parameter
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        return $this->render('events/show.html.twig', [ // Updated template path
            'events' => $events, // Updated variable name
        ]);
    }


    #[Route('/{id}/edit', name: 'app_events_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Events $event, SluggerInterface $slugger, EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $event->setUser($admin);
        // Récupération de l'image actuelle
        $currentImage = $event->getImage();

        $form = $this->createForm(EventsType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                // Suppression de l'ancienne image
                if ($currentImage) {
                    $oldFilePath = $this->getParameter('images_directory') . '/' . $currentImage;
                    if (file_exists($oldFilePath)) {
                        unlink($oldFilePath);
                    }
                }

                // Génération du nouveau nom de fichier
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                    $event->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de l\'image');
                    return $this->redirectToRoute('app_events_edit', ['id' => $event->getId()]);
                }
            }

            // Gestion de la suppression d'image


            $entityManager->flush();
            $this->addFlash('success', 'Événement mis à jour avec succès');
            return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('events/edit.html.twig', [
            'event' => $event,
            'form' => $form->createView(),
            'currentImage' => $currentImage
        ]);
    }
    #[Route('/yassine', name: 'app_yassine_index', methods: ['GET'])]
    public function index1(Request $request, EventsRepository $eventsRepository, TransportRepository $transportRepository): Response
    {
        $user = $this->getUser(); // Récupérer l'utilisateur connecté

        // Vérifier si l'utilisateur est authentifié et bloquer les admins
        if (!$user) {
            return $this->redirectToRoute('app_login');
        }  if (in_array('ROLE_ADMIN', $user->getRoles())) {
            // Si l'utilisateur est un admin, le rediriger ailleurs (par exemple, vers la page d'accueil)
            return $this->redirectToRoute('app_login'); // Remplacer 'app_home' par la route de votre page d'accueil
        }
        $session = $request->getSession();
        $reservedIds = $session->get('reserved_events', []);
    
        $filters = [
            'minPrice' => $request->query->get('minPrice'),
            'maxPrice' => $request->query->get('maxPrice'),
            'eventType' => $request->query->get('eventType'),
            'lieu' => $request->query->get('lieu'),
            'typetransport' => $request->query->get('typetransport', null),
            'minDate' => $request->query->get('minDate'),
            'maxDate' => $request->query->get('maxDate'),
            'minEndDate' => $request->query->get('minEndDate'),
            'maxEndDate' => $request->query->get('maxEndDate')
        ];
    
        // Récupération des événements triés
        $events = $eventsRepository->findSortedEvents($reservedIds, $filters);
    
        return $this->render('events/affichecard.html.twig', [
            'events' => $events,
            'eventTypes' => $eventsRepository->findAvailableEventTypes(),
            'transportTypes' => $transportRepository->findAllTransportTypes(),
            'currentFilters' => array_filter($filters)
        ]);
    }
    
    

    #[Route('/{id}', name: 'app_events_delete', methods: ['POST'])]
    public function delete(Request $request, Events $events, EntityManagerInterface $entityManager): Response // Updated method parameter
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        if ($this->isCsrfTokenValid('delete' . $events->getId(), $request->getPayload()->getString('_token'))) { // Updated variable name
            $entityManager->remove($events); // Updated variable name
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_events_index', [], Response::HTTP_SEE_OTHER); // Updated redirect route
    }
    #[Route('/event/reserve/{id}', name: 'app_event_reserve')]
public function reserve($id, Request $request, EventsRepository $eventsRepository): Response
{
    $session = $request->getSession();
    $reservedEvents = $session->get('reserved_events', []);

    // Vérifier si l'événement existe
    $event = $eventsRepository->find($id);
    if (!$event) {
        throw $this->createNotFoundException('Événement non trouvé');
    }

    // Ajouter l'ID seulement s'il n'existe pas déjà
    if (!in_array($id, $reservedEvents)) {
        $reservedEvents[] = $id;
        $session->set('reserved_events', $reservedEvents);
        $this->addFlash('success', 'Événement réservé avec succès !');
    }

    return $this->redirectToRoute('app_reserved_events');
}

#[Route('/mes-reservations', name: 'app_reserved_events')]
public function showReservedEvents(Request $request, EventsRepository $eventsRepository): Response
{
    $user = $this->getUser(); // Récupérer l'utilisateur connecté

    // Vérifier si l'utilisateur est authentifié et bloquer les admins
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }  if (in_array('ROLE_ADMIN', $user->getRoles())) {
        // Si l'utilisateur est un admin, le rediriger ailleurs (par exemple, vers la page d'accueil)
        return $this->redirectToRoute('app_login'); // Remplacer 'app_home' par la route de votre page d'accueil
    }
    $session = $request->getSession();
    $reservedIds = $session->get('reserved_events', []);
    
    // Récupérer les événements réservés
    $events = $eventsRepository->findBy(['id' => $reservedIds]);

    return $this->render('events/reserved_events.html.twig', [
        'events' => $events
    ]);
}
#[Route('/remove-reservation/{id}', name: 'app_remove_reservation')]
public function removeReservation($id, Request $request): Response
{
    
    $user = $this->getUser(); // Récupérer l'utilisateur connecté

    // Vérifier si l'utilisateur est authentifié et bloquer les admins
    if (!$user) {
        return $this->redirectToRoute('app_login');
    }  if (in_array('ROLE_ADMIN', $user->getRoles())) {
        // Si l'utilisateur est un admin, le rediriger ailleurs (par exemple, vers la page d'accueil)
        return $this->redirectToRoute('app_login'); // Remplacer 'app_home' par la route de votre page d'accueil
    }
    $session = $request->getSession();
    $reservedEvents = $session->get('reserved_events', []);

    // Supprimer l'ID de la liste
    $key = array_search($id, $reservedEvents);
    if ($key !== false) {
        unset($reservedEvents[$key]);
        $session->set('reserved_events', array_values($reservedEvents));
        $this->addFlash('success', 'Réservation annulée !');
    }

    return $this->redirectToRoute('app_reserved_events');
}
}
