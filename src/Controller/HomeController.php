<?php

namespace App\Controller;

use App\Repository\PostRepository;
use App\Form\PostType;
use App\Form\CommentType;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Like;
use App\Form\YoutubeType;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpClient\HttpClient;

final class HomeController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('/listposts', name: 'app_home')]
    public function index(Request $request, PostRepository $postRepository): Response
    {
        // Récupérer les filtres depuis la requête GET
        $filters = [
            'search' => $request->query->get('search', ''),
            'lieu' => $request->query->get('lieu', ''),
            'startDate' => $request->query->get('startDate', ''),
            'endDate' => $request->query->get('endDate', '')
        ];

        // Récupérer les posts filtrés
        $posts = $postRepository->findFilteredPosts($filters);
        $commentForms = [];

        $user = $this->getUser(); // Utilisateur connecté

        foreach ($posts as $post) {
            $comment = new Comment();
            $commentForm = $this->createForm(CommentType::class, $comment);
            $commentForms[$post->getId()] = $commentForm->createView();

            // Vérifier si l'utilisateur a liké le post
            $likedByUser = false;
            if ($user) {
                $likedByUser = $this->em->getRepository(Like::class)
                    ->findOneBy(['user' => $user, 'post' => $post]) !== null;
            }

            // Ajouter l'état du like au post
            $post->setLikedByUser($likedByUser);
        }

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'commentForms' => $commentForms,
            'user' => $user,
            'currentFilters' => $filters, // Pour réafficher les filtres saisis
        ]);
    }



    #[Route('/map', name: 'map')]
    public function mapdid(): Response
    {
        return $this->render('home/map.html.twig');
    }

    #[Route('/creerPost', name: 'app_createPost')]
    public function createPost(Request $request, SluggerInterface $slugger, ParameterBagInterface $params): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        $user = $this->getUser();  // Symfony's getUser() method retrieves the currently authenticated user

        if ($user) {
            // Assign the logged-in user to the post
            $post->setUser($user);
        } else {
            // If no user is logged in, you might want to handle this case (e.g., redirect or show an error)
            $this->addFlash('error', 'You must be logged in to create a post.');
            return $this->redirectToRoute('app_login');  // Redirect to login page if the user is not logged in
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();
            if ($imageFile) {
                $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/gif'];
                $mimeType = $imageFile->getMimeType();
                if (!in_array($mimeType, $allowedMimeTypes)) {
                    $this->addFlash('error', 'Seuls les fichiers JPEG, PNG et GIF sont autorisés.');
                    return $this->redirectToRoute('app_createPost');
                }
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('images_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Une erreur est survenue lors du téléchargement de l\'image.');
                    return $this->redirectToRoute('app_createPost');
                }

                $post->setImage($newFilename);
            }

            $this->em->persist($post);
            $this->em->flush();
            $this->addFlash('message', 'Post créé avec succès!');
            return $this->redirectToRoute('app_home');
        }
        return $this->render('home/post.html.twig', [
            'form' =>  $form->createView()
        ]);
    }

    #[Route('/editPost/{id}', name: 'app_editPost')]
    public function editPost(Request $request, $id, SluggerInterface $slugger)
    {
        $post = $this->em->getRepository(Post::class)->find($id);
        $currentImage = $post->getImage(); // Store old image

        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $imageFile */
            $imageFile = $form->get('image')->getData();

            if ($imageFile) { // If user uploads a new file
                $newFilename = uniqid() . '.' . $imageFile->guessExtension();
                $imageFile->move($this->getParameter('images_directory'), $newFilename);
                $post->setImage($newFilename);
            } else {
                $post->setImage($currentImage); // Keep old image
            }

            $this->em->flush();
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/post.html.twig', [
            'form' => $form->createView(),
            'post' => $post,
            'image' => $currentImage ? '/uploads/' . $currentImage : null,
        ]);
    }
    #[Route('/deletePost/{id}', name: 'app_deletePost')]
    public function deletePost($id): Response
    {
        $post = $this->em->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException('Post introuvable');
        }

        // Supprimer d'abord les likes associés au post
        $postLikes = $this->em->getRepository(Like::class)->findBy(['post' => $post]);
        foreach ($postLikes as $like) {
            $this->em->remove($like);
        }

        // Ensuite, supprimer le post
        $this->em->remove($post);
        $this->em->flush();

        $this->addFlash('message', 'Post supprimé avec succès!');

        return $this->redirectToRoute('app_home');
    }

    #[Route('/post/{id}/like', name: 'post_like', methods: ['POST'])]
    public function likePost(int $id): JsonResponse
    {
        $user = $this->getUser();
        if (!$user) {
            return new JsonResponse(['error' => 'You must be logged in to like a post'], 401);
        }

        $post = $this->em->getRepository(Post::class)->find($id);
        if (!$post) {
            return new JsonResponse(['error' => 'Post not found'], 404);
        }

        // Check if the user has already liked the post
        $existingLike = $this->em->getRepository(Like::class)->findOneBy([
            'user' => $user,
            'post' => $post
        ]);

        if ($existingLike) {
            // Unlike: Remove the like and decrement count
            $this->em->remove($existingLike);
            $post->setLikes(max(0, $post->getLikes() - 1)); // Ensure it never goes below 0
            $liked = false;
        } else {
            // Like: Add the like and increment count
            $like = new Like($user, $post);
            $this->em->persist($like);
            $post->setLikes($post->getLikes() + 1);
            $liked = true;
        }

        // Save changes to the database
        $this->em->flush();

        // Return the updated like status and count
        return new JsonResponse([
            'liked' => $liked, // Current like status (true if liked, false if unliked)
            'likes' => $post->getLikes(), // Updated like count
        ]);
    }

    private function hasBadWords(string $content): bool
    {
        // List of bad words (You can expand this list further as needed)
        $badWords = [
            'kill',
            'murder',
            'suicide',
            'violence',
            'abuse',
            'rape',
            'hate',
            'bomb',
            'terror',
            'drugs',
            'cocaine',
            'heroin',
            'weed',
            'meth',
            'crack',
            'gun',
            'shoot',
            'stabb',
            'assault',
            'war',
            'killing',
            'slaughter',
            'threat'
        ];

        // Check if any of the bad words exist in the content (case-insensitive search)
        foreach ($badWords as $badWord) {
            if (stripos($content, $badWord) !== false) {
                return true;
            }
        }

        return false;
    }

    private function checkForBadWords(string $content): bool
    {
        try {

            if ($this->hasBadWords($content)) {
                return true;
            }

            $githubToken = $this->getParameter('github_token');
            if (empty($githubToken)) {
                throw new \Exception('GitHub token is not configured');
            }

            $url = 'https://models.inference.ai.azure.com/chat/completions';
            $data = [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a content moderator. Analyze the following text and respond with only "true" if it contains inappropriate language, profanity, or offensive content, or "false" if it is clean.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $content
                    ]
                ],
                'model' => 'Codestral-2501'
            ];
            $jsonData = json_encode($data);

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $githubToken,
                'x-ms-model-mesh-model-name: Codestral-2501'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if (curl_errno($ch)) {
                throw new \Exception('cURL error: ' . curl_error($ch));
            }

            curl_close($ch);

            if ($statusCode !== 200) {
                throw new \Exception('API request failed with status: ' . $statusCode . ' and response: ' . $response);
            }

            $decodedResponse = json_decode($response, true);
            if (!isset($decodedResponse['choices'][0]['message']['content'])) {
                throw new \Exception('Unexpected API response format');
            }

            $result = strtolower(trim($decodedResponse['choices'][0]['message']['content']));
            return $result === 'true';
        } catch (\Exception $e) {
            error_log('Bad word detection failed: ' . $e->getMessage());
            return false;
        }
    }

    #[Route('/post/{id}/comment', name: 'post_comment', methods: ['GET', 'POST'])]
    public function addComment(int $id, Request $request): Response
    {
        try {
            $post = $this->em->getRepository(Post::class)->find($id);
            if (!$post) {
                throw $this->createNotFoundException('Post not found');
            }

            if ($request->isMethod('GET')) {
                // Handle GET request - maybe return the comment form or existing comments
                return $this->redirectToRoute('app_home');
            }

            // Continue with POST handling
            $data = [];
            parse_str($request->getContent(), $data);

            if (empty($data['comment']['content'])) {
                $this->addFlash('error', 'Comment content cannot be empty');
                return $this->redirectToRoute('app_home');
            }

            if ($this->checkForBadWords($data['comment']['content'])) {
                $this->addFlash('error', 'Your comment contains inappropriate content.');
                return $this->redirectToRoute('app_home');
            }

            $comment = new Comment();
            $comment->setContent(trim($data['comment']['content']));
            $comment->setPost($post);
            $comment->setCreatedAt(new \DateTime());
            $user = $this->getUser();
            if (!$user) {
                $this->addFlash('error', 'You must be logged in to comment');
                return $this->redirectToRoute('app_login');
            }
            $comment->setUser($user);

            $this->em->persist($comment);
            $this->em->flush();

            $this->addFlash('success', 'Comment added successfully!');
            return $this->redirectToRoute('app_home');
        } catch (\Exception $e) {
            $this->addFlash('error', $e->getMessage());
            return $this->redirectToRoute('app_home');
        }
    }

    #[Route("/comment/update/{id}", name: "comment_update", methods: ["POST", "PUT"])]
    public function updateComment(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $comment = $entityManager->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        if ($comment->getUser() !== $this->getUser()) {
            $this->addFlash('error', 'You are not authorized to update this comment');
            return $this->redirectToRoute('app_home');
        }

        $newContent = $request->request->get('content');
        if (!$newContent) {
            $this->addFlash('error', 'Comment content cannot be empty');
            return $this->redirectToRoute('app_home');
        }

        // Check for bad words in the updated content
        if ($this->checkForBadWords($newContent)) {
            $this->addFlash('error', 'Your comment contains inappropriate content.');
            return $this->redirectToRoute('app_home');
        }

        $comment->setContent($newContent);
        $entityManager->flush();

        $this->addFlash('success', 'Comment updated successfully!');
        return $this->redirectToRoute('app_home');
    }

    #[Route('/neww/{id}', name: 'comment_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, int $id): Response
    {
        $post = $entityManager->getRepository(Post::class)->find($id);

        if (!$post) {
            throw $this->createNotFoundException("Post not found for ID $id");
        }
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        $user = $this->getUser();
        if ($user) {
            // Assign the logged-in user to the post
            $comment->setUser($user);
        } else {
            // If no user is logged in, you might want to handle this case (e.g., redirect or show an error)
            $this->addFlash('error', 'You must be logged in to create a post.');
            return $this->redirectToRoute('app_login');  // Redirect to login page if the user is not logged in
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setPost($post);
            $entityManager->persist($comment);
            $entityManager->flush();

            $this->addFlash('success', 'Comment added successfully!');
            return $this->redirectToRoute('app_home');
        } else {
            $this->addFlash('danger', 'votre commentaire est vide !');
            return $this->redirectToRoute('app_home');
        }

        return $this->render('home/comment.html.twig', [
            'form' => $form->createView(),
            'post' => $post, // Pass the post to Twig

        ]);
    }

    #[Route('/comment/delete/{id}', name: 'comment_delete', methods: ['GET', 'POST'])]
    public function deleteComment(int $id, EntityManagerInterface $entityManager): Response
    {
        $comment = $entityManager->getRepository(Comment::class)->find($id);

        if (!$comment) {
            throw $this->createNotFoundException('Comment not found');
        }

        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_home'); // Change this to your actual post listing route
    }

    #[Route('/listposts/filtered', name: 'app_home_filtered')]
    public function indexx(Request $request, PostRepository $postRepository): Response
    {
        $filters = [
            'search' => $request->query->get('search', ''), // Défaut : chaîne vide
            'lieu' => $request->query->get('lieu', ''),
            'startDate' => $request->query->get('startDate', ''),
            'endDate' => $request->query->get('endDate', '')
        ];

        $posts = $postRepository->findFilteredPosts($filters);

        return $this->render('home/index.html.twig', [
            'posts' => $posts,
            'currentFilters' => $filters, // S'assurer que cette ligne est bien présente
        ]);
    }

    #[Route('/translate-post', name: 'translate_post', methods: ['POST'])]
    public function translatePost(Request $request): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            $content = $data['content'] ?? '';
            $targetLang = $data['targetLang'] ?? 'fr';

            // Debug log
            error_log("Translation request: " . json_encode($data));

            if (empty($content)) {
                throw new \Exception('No content provided for translation');
            }

            $githubToken = $this->getParameter('github_token');
            if (empty($githubToken)) {
                throw new \Exception('GitHub token is not configured');
            }

            $url = 'https://models.inference.ai.azure.com/chat/completions';
            $prompt = "Translate the following text to " . $this->getLanguageName($targetLang) .
                ". Respond ONLY with the direct translation, no explanations or additional text:\n\n" . $content;

            $data = [
                'messages' => [
                    [
                        'role' => 'system',
                        'content' => 'You are a translation assistant. Provide direct translations only.'
                    ],
                    [
                        'role' => 'user',
                        'content' => $prompt
                    ]
                ],
                'model' => 'gpt-4o',
                'temperature' => 0.3, // Lower temperature for more consistent translations
                'max_tokens' => 1000
            ];

            // Debug log
            error_log("API request: " . json_encode($data));

            $jsonData = json_encode($data);
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $githubToken,
                'x-ms-model-mesh-model-name: gpt-4o'
            ]);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

            $response = curl_exec($ch);
            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Debug log
            error_log("API response: " . $response);
            error_log("Status code: " . $statusCode);

            if (curl_errno($ch)) {
                throw new \Exception('cURL error: ' . curl_error($ch));
            }

            curl_close($ch);

            if ($statusCode !== 200) {
                throw new \Exception("API request failed with status: $statusCode and response: $response");
            }

            $decodedResponse = json_decode($response, true);
            if (!isset($decodedResponse['choices'][0]['message']['content'])) {
                throw new \Exception('Unexpected API response format: ' . json_encode($decodedResponse));
            }

            $translation = trim($decodedResponse['choices'][0]['message']['content']);
            return new JsonResponse(['translation' => $translation]);
        } catch (\Exception $e) {
            error_log("Translation error: " . $e->getMessage());
            return new JsonResponse([
                'error' => $e->getMessage(),
                'details' => 'Check server logs for more information'
            ], 500);
        }
    }

    private function getLanguageName(string $code): string
    {
        $languages = [
            'fr' => 'French',
            'en' => 'English',
            'ar' => 'Arabic',
            'es' => 'Spanish',
            'de' => 'German'
        ];

        return $languages[$code] ?? 'French';
    }
}
