<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class EmailService
{
    private MailerInterface $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendCollabEndingEmail(string $email, string $restaurantName)
    {
        $email = (new Email())
            ->from('ooyosri@gmail.com')
            ->to($email)
            ->subject('Votre collaboration arrive à expiration')
            ->text("Bonjour, la collaboration avec le restaurant '$restaurantName' prend fin bientôt. Merci de nous contacter pour un renouvellement.");

        $this->mailer->send($email);
    }
}
