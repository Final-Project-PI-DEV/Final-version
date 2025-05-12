<?php

namespace App\Controller;

use App\Entity\Events;
use App\Repository\EventsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session as StripeSession;
use Stripe\Webhook;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCodeBundle\Response\QrCodeResponse;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\User;

class PaymentController extends AbstractController
{
    #[Route('/checkout', name: 'checkout', methods: ['GET'])]
    public function checkout(SessionInterface $session): Response
    {
        $panier = $session->get('panier', []);

        if (empty($panier)) {
            return $this->redirectToRoute('panier_index');
        }

        $lineItems = [];

        foreach ($panier as $type => $items) {
            foreach ($items as $itemId => $itemData) {
                // Adaptation pour les différents types d'items
                $productName = $type === 'events'
                    ? $itemData['item']->getTitre()
                    : $itemData['item']->getName();

                $lineItems[] = [
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => (int)($itemData['price'] * 100),
                        'product_data' => [
                            'name' => $productName,
                            // Optionnel : ajouter une description spécifique
                            'description' => $type === 'events'
                                ? 'Événement: ' . $itemData['item']->getLieu()
                                : null,
                        ],
                    ],
                    'quantity' => $itemData['quantity'],
                ];
            }
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        try {
            $checkoutSession = StripeSession::create([
                'payment_method_types' => ['card'],
                'line_items' => $lineItems,
                'mode' => 'payment',
                'success_url' => $this->generateUrl('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL),
                'cancel_url' => $this->generateUrl('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
            ]);

            return $this->redirect($checkoutSession->url);
        } catch (\Exception $e) {
            $this->addFlash('error', 'Erreur de paiement : ' . $e->getMessage());
            return $this->redirectToRoute('panier');
        }
    }

    #[Route('/payment/success', name: 'payment_success', methods: ['GET'])]
    public function success(SessionInterface $session , Security $security): Response
    {
        $user = $security->getUser();
        if ($user instanceof User) {
            $userName = $user->getNom();
            $userLastName = $user->getPrenom();
        } else {
            $userName = 'Inconnu';
            $userLastName = 'Inconnu';
        }
        $data = $session->get('panier') ?? ' https://307b-196-234-18-173.ngrok-free.app/payment/success';
        $session->remove('panier');
        $user = $security->getUser();

    // Vérifier si l'utilisateur est connecté et est une instance de User
  

    // Construire les données du QR code
    $qrData = json_encode([
        'panier' => $data,
        'first_name' => $userName,
        'last_name' => $userLastName
    ]);

        $result = new Builder(
            data: $data,
        );
        return $this->render(
            'payment/success.html.twig',
            [
                'qrcode' => $result->build()->getDataUri(),
                'user_name' => $userName,
                 'user_last_name' => $userLastName,
                 
            ],

        );
    }

    #[Route('/payment/cancel', name: 'payment_cancel', methods: ['GET'])]
    public function cancel(): Response
    {
        return $this->render('payment/cancel.html.twig');
    }

    #[Route('/stripe-webhook', name: 'stripe_webhook', methods: ['POST'])]
    public function stripeWebhook(Request $request): Response
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);
        $endpoint_secret = $_ENV['STRIPE_WEBHOOK_SECRET'] ?? '';

        $payload = $request->getContent();
        $sig_header = $request->headers->get('stripe-signature');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException | \Stripe\Exception\SignatureVerificationException $e) {
            return new Response('Webhook error: ' . $e->getMessage(), 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session = $event->data->object;
            // Logique pour enregistrer la commande en base de données
        }

        return new Response('Webhook reçu', 200);
    }
}
