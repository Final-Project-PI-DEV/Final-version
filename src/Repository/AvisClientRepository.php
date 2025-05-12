<?php

namespace App\Repository;

use App\Entity\AvisClient;
use App\Entity\Reclamation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AvisClientRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AvisClient::class);
    }

    public function getStatistiques(): array
    {
        $avis = $this->findAll();
        $stats = [
            'total' => count($avis),
            'questions' => [
                0 => ['vrai' => 0, 'faux' => 0],
                1 => ['vrai' => 0, 'faux' => 0],
                2 => ['vrai' => 0, 'faux' => 0],
                3 => ['vrai' => 0, 'faux' => 0],
                4 => ['vrai' => 0, 'faux' => 0],
            ]
        ];

        foreach ($avis as $avisClient) {
            $reponses = $avisClient->getReponses();
            foreach ($reponses as $index => $reponse) {
                if ($reponse) {
                    $stats['questions'][$index]['vrai']++;
                } else {
                    $stats['questions'][$index]['faux']++;
                }
            }
        }

        return $stats;
    }

    public function hasAvisForReclamation(Reclamation $reclamation): bool
    {
        $qb = $this->createQueryBuilder('a')
            ->select('COUNT(a.id)')
            ->where('a.reclamation = :reclamation')
            ->setParameter('reclamation', $reclamation);

        return (bool)$qb->getQuery()->getSingleScalarResult();
    }
}
