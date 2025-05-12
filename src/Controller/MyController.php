<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Post;
use App\Entity\Comment;
use App\Repository\LoisirRepository;
use App\Repository\MaterielRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use  App\Repository\RestaurantRepository;
use Knp\Component\Pager\PaginatorInterface;

final class MyController extends AbstractController
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    #[Route('/my', name: 'app_my')]
    public function index(): Response
    {
        return $this->render('my/index.html.twig', [
            'controller_name' => 'MyController',
        ]);
    }
    #[Route('/homee', name: 'home1_app')]
    public function index2(): Response
    {
        return $this->render('home1.html.twig');
    }
    #[Route('/sign-in', name: 'sign-in')]
    public function index3(): Response
    {
        return $this->render('aut.html.twig');
    }
    #[Route('/test', name: 'test')]
    public function index4(): Response
    {
        $user = $this->getUser();

        return $this->render('index1.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/loisir', name: 'loisir')]
    public function index9(): Response
    {
        return $this->render('loisir.html.twig');
    }
    #[Route('/link1', name: 'link1')]
    public function index10(): Response
    {
        return $this->render('link1.html.twig');
    }
    #[Route('/link2', name: 'link2')]
    public function index11(): Response
    {
        return $this->render('link2.html.twig');
    }
    #[Route('/link3', name: 'link3')]
    public function index12(): Response
    {
        return $this->render('link3.html.twig');
    }
    #[Route('/post/delete/{id}', name: 'delete_post')]
    public function deletePost($id): RedirectResponse
    {
        $post = $this->em->getRepository(Post::class)->find($id);

        if (!$post) {
            $this->addFlash('danger', 'Post non trouvé!');
            return $this->redirectToRoute('app_listPostA');
        }

        $this->em->remove($post);
        $this->em->flush();

        $this->addFlash('success', 'Post supprimé avec succès!');
        return $this->redirectToRoute('app_listPostA');
    }
    #[Route('/comment/delete/{id}', name: 'delete_comment')]
    public function deleteComment($id): RedirectResponse
    {
        $comment = $this->em->getRepository(Comment::class)->find($id);

        if (!$comment) {
            $this->addFlash('danger', 'Commentaire non trouvé!');
            error_log("Redirecting to app_listPostA because comment was not found.");
            return $this->redirectToRoute('app_listPostA');
        }

        $this->em->remove($comment);
        $this->em->flush();
        $this->addFlash('success', 'Commentaire supprimé avec succès!');
        error_log("Redirecting to app_listPostA after successful comment deletion.");
        return $this->redirectToRoute('app_listPostA');
    }
    #[Route('/listPost', name: 'app_listPostA')]
    public function listPostsAdmin(): Response
    {
        $admin = $this->getUser();

        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('home11');
        }
        $posts = $this->em->getRepository(Post::class)->findAll();
        return $this->render('home/listPostAdmin.html.twig', [
            'posts' => $posts,
        ]);
    }
    #[Route('/dashboardAdmin', name: 'dashboard')]
    public function index13(EntityManagerInterface $entityManager): Response
    {
        $admin = $this->getUser();
        if (!$this->isGranted('ROLE_ADMIN')) {
            // Redirect the user to home if they are not an admin
            return $this->redirectToRoute('dashboard');
        }
        $posts = $entityManager->getRepository(Post::class)->findAll();
    
        return $this->render('dashboard.html.twig', [
            'posts' => $posts,
            'admin' => $admin
        ]);
    }
    
    #[Route('/materiel-admin', name: 'app_materiel_admin')]
    public function matAdmin(MaterielRepository $materielRepository): Response
    {
        $materiels = $materielRepository->findAll();
        $admin = $this->getUser();

        return $this->render('materiel/dashboard.html.twig', [
            'materiels' => $materiels,
            'admin' => $admin
        ]);
    }
    #[Route('/loisir-admin', name: 'app_loisir_admin')]
    public function index1(LoisirRepository $loisirRepository): Response
    {
        $admin = $this->getUser();
        $loisirs = $loisirRepository->findAll();

        return $this->render('loisir/dashboard.html.twig', [
            'loisirs' => $loisirs,
            'admin' => $admin
        ]);
    }
    #[Route('/client', name: 'client')]
    public function client(MaterielRepository $materielRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $materielRepository->createQueryBuilder('m')->getQuery();
        
        $materiels = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            1 // Nombre d'éléments par page
        );
    
        return $this->render('materiel/client.html.twig', [
            'materiels' => $materiels
        ]);
    }
    
    #[Route('/clientL', name: 'clientL')]
    public function clientL(LoisirRepository $loisirRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $loisirRepository->createQueryBuilder('l')->getQuery();
        
       
    
        $loisirs = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            2 // Nombre d'éléments par page
        );
    
        return $this->render('loisir/client.html.twig', [
            
            'loisirs' => $loisirs
        ]);
    }

    #[Route('/test-email', name: 'test_email')]
    public function sendEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('ooyosri@gmail.com')
            ->to('hmissiwadhah@gmail.com')
            ->subject('TEST TEst')
            ->text('Congratulations hddd, you just sent an email with Mailgun! You are truly awesome!');

        try {
            $mailer->send($email);
            $message = 'Email envoyé avec succès !';
        } catch (\Exception $e) {
            $message = 'Erreur lors de l\'envoi de l\'email: ' . $e->getMessage();
        }

        return new Response($message);
    }
    #[Route('/resto', name: 'resto')]
    public function front(RestaurantRepository $restaurantRepository): Response
    {
        return $this->render('restaurant/front.html.twig', [
            'restaurants' => $restaurantRepository->findAll(),
        ]);
    }
}
