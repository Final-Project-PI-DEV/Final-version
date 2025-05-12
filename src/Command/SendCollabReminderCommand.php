<?php
// src/Command/SendCollabReminderCommand.php
namespace App\Command;

use App\Repository\RestaurantRepository;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:send-collab-reminders')]
class SendCollabReminderCommand extends Command
{
    private RestaurantRepository $restaurantRepository;
    private EmailService $emailService;
    private EntityManagerInterface $entityManager;

    public function __construct(RestaurantRepository $restaurantRepository, EmailService $emailService, EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->restaurantRepository = $restaurantRepository;
        $this->emailService = $emailService;
        $this->entityManager = $entityManager;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $today = new \DateTime();
        $restaurants = $this->restaurantRepository->findBy([
            'statut' => 'actif',
        ]);

        foreach ($restaurants as $restaurant) {
            if ($restaurant->getDateFinColab() <= $today) {
                $this->emailService->sendCollabEndingEmail($restaurant->getEmail(), $restaurant->getNomResto());
                $restaurant->setStatut('inactif');
                $this->entityManager->flush();
            }
        }

        $output->writeln('Emails envoyés avec succès.');
        return Command::SUCCESS;
    }
}
