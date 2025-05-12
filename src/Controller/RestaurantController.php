<?php

namespace App\Controller;

use App\Entity\Restaurant;
use App\Form\RestaurantType;
use App\Repository\RestaurantRepository;
use App\Repository\MenuRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use App\Service\EmailService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use App\Service\PredictionService;




#[Route('/restaurant')]
final class RestaurantController extends AbstractController
{
    /* #[Route(name: 'app_restaurant_index', methods: ['GET'])]
    public function index(RestaurantRepository $restaurantRepository): Response
    {
        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurantRepository->findAll(),
        ]);
    }

    //front
    #[Route('/front', name: 'app_restaurant_front', methods: ['GET'])]
    public function front(RestaurantRepository $restaurantRepository): Response
    {
        return $this->render('restaurant/front.html.twig', [
            'restaurants' => $restaurantRepository->findAll(),
        ]);
    }*/

    #[Route(name: 'app_restaurant_index', methods: ['GET'])]
    public function index(RestaurantRepository $restaurantRepository, EntityManagerInterface $entityManager): Response
    {
        $restaurants = $restaurantRepository->findAll();

        foreach ($restaurants as $restaurant) {
            $restaurant->updateStatus();
            $entityManager->flush(); // Sauvegarde les modifications
        }

        return $this->render('restaurant/index.html.twig', [
            'restaurants' => $restaurants,
        ]);
    }

    #[Route('/front', name: 'app_restaurant_front', methods: ['GET'])]
    public function front(RestaurantRepository $restaurantRepository, EntityManagerInterface $entityManager): Response
    {
        $restaurants = $restaurantRepository->findAll();

        foreach ($restaurants as $restaurant) {
            $restaurant->updateStatus();
            $entityManager->flush();
        }

        return $this->render('restaurant/front.html.twig', [
            'restaurants' => $restaurants,
        ]);
    }

    //
    #[Route('/accueil', name: 'app_restaurant_accueil', methods: ['GET'])]
    public function accueil(RestaurantRepository $restaurantRepository, EntityManagerInterface $entityManager): Response
    {
        // Récupérer tous les restaurants
        $restaurants = $restaurantRepository->findAll();

        // Mettre à jour le statut de chaque restaurant
        foreach ($restaurants as $restaurant) {
            $restaurant->updateStatus();
            $entityManager->flush();
        }

        // Calculer le score prédit pour chaque restaurant
        $predictions = [];
        foreach ($restaurants as $restaurant) {
            $rating = $restaurant->getAverageRating();
            $predictedScore = $rating * 1.2; // Exemple de calcul du score prédit
            $predictions[] = [
                'restaurant' => $restaurant,
                'predictedScore' => $predictedScore,
            ];
        }

        // Trier les restaurants par score prédit (du plus élevé au plus bas)
        usort($predictions, function ($a, $b) {
            return $b['predictedScore'] <=> $a['predictedScore'];
        });

        // Sélectionner les 3 premiers restaurants
        $topRestaurants = array_slice($predictions, 0, 3);

        // Passer les 3 meilleurs restaurants au template
        return $this->render('restaurant/accueil.html.twig', [
            'restaurants' => array_map(function ($prediction) {
                return $prediction['restaurant'];
            }, $topRestaurants),
        ]);
    }

    #[Route('/best', name: 'app_restaurant_best', methods: ['GET'])]
    public function best(PredictionService $predictionService): Response
    {
        $predictions = $predictionService->predictBestRestaurants();

        // Afficher les résultats dans le template
        return $this->render('restaurant/best.html.twig', [
            'predictions' => $predictions,
        ]);
    }

    #[Route('/commander', name: 'app_restaurant_commander', methods: ['POST'])]
    public function commander(Request $request): JsonResponse
    {
        // Récupérer les données de la commande depuis la requête
        $data = json_decode($request->getContent(), true);

        // Générer un ID unique pour la commande
        $commandeId = uniqid('commande_');

        // Calculer le total de la commande
        $total = 0;
        foreach ($data['menus'] as $menu) {
            $total += $menu['quantity'] * $menu['price'];
        }

        // Stocker la commande en session
        $session = $request->getSession();
        $session->set($commandeId, [
            'menus' => $data['menus'],
            'total' => $total,
            'restaurant_id' => $data['restaurant_id'],
        ]);

        // Retourner une réponse JSON avec l'ID de la commande et le total
        return new JsonResponse([
            'success' => true,
            'commandeId' => $commandeId,
            'total' => $total,
        ]);

        // Retourner une réponse JSON avec l'ID de la commande et l'URL des détails
        return new JsonResponse([
            'success' => true,
            'commandeId' => $commandeId,
            'total' => $total,
            'detailsUrl' => $this->generateUrl('app_restaurant_commande_details', ['id' => $commandeId]),
        ]);
    }


    #[Route('/mes-commandes', name: 'app_restaurant_mes_commandes', methods: ['GET'])]
    public function mesCommandes(Request $request): Response
    {
        // Récupérer la session
        $session = $request->getSession();

        // Récupérer tous les IDs de commandes stockés en session
        $commandesIds = array_keys($session->all());

        // Filtrer pour ne garder que les IDs de commandes
        $commandesIds = array_filter($commandesIds, function ($key) {
            return strpos($key, 'commande_') === 0;
        });

        return $this->render('restaurant/mes_commandes.html.twig', [
            'commandesIds' => $commandesIds,
        ]);
    }



    /* #[Route('/new', name: 'app_restaurant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($restaurant);
            $entityManager->flush();

            return $this->redirectToRoute('app_restaurant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('restaurant/new.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
        ]);
    }*/
    private string $restaurantImagesDirectory;

    public function __construct(ParameterBagInterface $params)
    {
        $this->restaurantImagesDirectory = $params->get('restaurant_images_directory');
    }


    #[Route('/new', name: 'app_restaurant_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $restaurant = new Restaurant();
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile*/
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('restaurant_images_directory'),
                        $newFilename
                    );
                    $restaurant->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible de télécharger l\'image.');
                }
            }

            $entityManager->persist($restaurant);
            $entityManager->flush();

            return $this->redirectToRoute('app_restaurant_index');
        }

        return $this->render('restaurant/new.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
        ]);
    }


    //show lkdima
    /*#[Route('/{id}', name: 'app_restaurant_show', methods: ['GET'])]
    public function show(Restaurant $restaurant): Response
    {   
        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $restaurant->getMenu(),
        ]);
    }*/

    #[Route('/{id}', name: 'app_restaurant_show', methods: ['GET'])]
    public function show(int $id, Restaurant $restaurant, EntityManagerInterface $entityManager, RestaurantRepository $restaurantRepository): Response
    {
        $restaurant = $restaurantRepository->find($id);
        $restaurant->updateStatus();
        $entityManager->flush();
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        return $this->render('restaurant/show.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $restaurant->getMenu(),
        ]);
    }
    //show lkdima
    //front
    /*#[Route('/{id}', name: 'app_restaurant_show1', methods: ['GET'])]
    public function show1(Restaurant $restaurant): Response
    {
        return $this->render('restaurant/show1.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $restaurant->getMenu(),
        ]);
    }*/

    #[Route('/{id}', name: 'app_restaurant_show1', methods: ['GET'])]
    public function show1(Restaurant $restaurant, EntityManagerInterface $entityManager): Response
    {
        $restaurant->updateStatus();
        $entityManager->flush();

        return $this->render('restaurant/show1.html.twig', [
            'restaurant' => $restaurant,
            'menus' => $restaurant->getMenu(),
        ]);
    }

    /* #[Route('/{id}/edit', name: 'app_restaurant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_restaurant_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('restaurant/edit.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
        ]);
    }*/
    #[Route('/{id}/edit', name: 'app_restaurant_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(RestaurantType::class, $restaurant);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('restaurant_images_directory'),
                        $newFilename
                    );
                    $restaurant->setImage($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('danger', 'Impossible de télécharger l\'image.');
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_restaurant_index');
        }

        return $this->render('restaurant/edit.html.twig', [
            'restaurant' => $restaurant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_restaurant_delete', methods: ['POST'])]
    public function delete(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $restaurant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($restaurant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_restaurant_index', [], Response::HTTP_SEE_OTHER);
    }

    //recherche
    /*#[Route('/search', name: 'app_restaurant_search', methods: ['GET'])]
    public function search(Request $request, RestaurantRepository $restaurantRepository): Response
    {
        $keyword = $request->query->get('q', ''); // Récupère le paramètre "q" depuis l'URL

        $restaurants = [];
        if (!empty($keyword)) {
            $restaurants = $restaurantRepository->searchByNameOrSpecialty($keyword);
        }

        return $this->render('restaurant/search_results.html.twig', [
            'restaurants' => $restaurants,
            'keyword' => $keyword,
        ]);
    }*/

    #[Route('/send-email/{id}', name: 'app_restaurant_send_email', methods: ['GET'])]
    public function sendEmail(Restaurant $restaurant, EmailService $emailService, EntityManagerInterface $entityManager): RedirectResponse
    {
        if ($restaurant->getStatut() === 'inactif') {
            $emailService->sendCollabEndingEmail($restaurant->getEmail(), $restaurant->getNomResto());
            $this->addFlash('success', "Email envoyé à {$restaurant->getNomResto()} !");
        } else {
            $this->addFlash('warning', "Ce restaurant est encore actif, aucun email envoyé.");
        }

        return $this->redirectToRoute('app_restaurant_index');
    }


    /*#[Route('/{id}/map', name: 'app_restaurant_showmap', methods: ['GET'])]
    public function showMap(Restaurant $restaurant, EntityManagerInterface $entityManager, RestaurantRepository $restaurantRepository)
    {
        $restaurants = $restaurantRepository->findAll();
        $entityManager->flush();
        // Votre logique pour afficher la carte
        return $this->render('restaurant/showmap.html.twig', [
            'restaurants' => $restaurants,
        ]);
    }*/

    /*#[Route('/map/{id}', name: 'app_restaurant_map', methods: ['GET'])]
    public function map(int $id, RestaurantRepository $restaurantRepository): Response
    {
        // Récupérer le restaurant par son ID
        $restaurant = $restaurantRepository->find($id);

        // Vérifier si le restaurant existe
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        // Récupérer l'adresse du restaurant
        $adresse = $restaurant->getAdresse();

        // Passer les données à la vue
        return $this->render('restaurant/map.html.twig', [
            'restaurant' => $restaurant,
            'adresse' => $adresse,
            /*'google_maps_api_key' => $_ENV['GOOGLE_MAPS_API_KEY'], // Passer la clé API à Twig
        ]);
    }*/

    #[Route('/map/{id}', name: 'app_restaurant_map', methods: ['GET'])]
    public function map(int $id, RestaurantRepository $restaurantRepository): Response
    {
        // Récupérer le restaurant par son ID
        $restaurant = $restaurantRepository->find($id);

        // Vérifier si le restaurant existe
        if (!$restaurant) {
            throw $this->createNotFoundException('Restaurant non trouvé');
        }

        // Récupérer l'adresse du restaurant
        $adresse = $restaurant->getAdresse();

        // Passer les données à la vue
        return $this->render('restaurant/map.html.twig', [
            'restaurant' => $restaurant,
            'adresse' => $adresse,
        ]);
    }

    #[Route('/commande/{id}', name: 'app_restaurant_commande_details', methods: ['GET'])]
    public function commandeDetails(Request $request, string $id): Response
    {
        $session = $request->getSession();
        $commande = $session->get($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('restaurant/commande_details.html.twig', [
            'commande' => $commande,
            'commandeId' => $id,
        ]);
    }

    #[Route('/rate/{id}', name: 'app_restaurant_rate', methods: ['POST'])]
    public function rate(Request $request, Restaurant $restaurant, EntityManagerInterface $entityManager): JsonResponse
    {
        $rating = $request->request->get('rating');

        // Validation de la note
        if (!is_numeric($rating) || $rating < 1 || $rating > 5) {
            return new JsonResponse(['success' => false, 'message' => 'La note doit être comprise entre 1 et 5.'], 400);
        }

        // Mettre à jour la note moyenne
        $restaurant->updateAverageRating((float) $rating);

        // Enregistrer les modifications
        $entityManager->flush();

        return new JsonResponse([
            'success' => true,
            'averageRating' => $restaurant->getAverageRating(),
            'ratingCount' => $restaurant->getRatingCount(),
        ]);
    }
}
