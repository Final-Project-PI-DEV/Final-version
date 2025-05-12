<?php

namespace App\Repository;

use App\Entity\Events;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Events>
 */
class EventsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Events::class);
    }
    //query builder est utiliser pour creer des requete sql sans creer dans sql
        //pour cree querybuilder par createQuerybuilder
    public function findFilteredEvents(array $filters)
    {
        $qb = $this->createQueryBuilder('e')
        ->leftJoin('e.transport', 't')
        ->addSelect('t');

        if (!empty($filters['minPrice'])) {
            $qb->andWhere('e.prix >= :minPrice')
               ->setParameter('minPrice', $filters['minPrice']);
        }
    
        if (!empty($filters['maxPrice'])) {
            $qb->andWhere('e.prix <= :maxPrice')
               ->setParameter('maxPrice', $filters['maxPrice']);
        }
        if (!empty($filters['typetransport'])) {
            $qb->andWhere('LOWER(t.typetransport) = LOWER(:typetransport)')
               ->setParameter('typetransport', $filters['typetransport']);
        }
        if (!empty($filters['minDate'])) {
            $minDate = new \DateTime($filters['minDate']);
            $qb->andWhere('e.date_debut >= :minDate')
               ->setParameter('minDate', $minDate);
        }
    
        if (!empty($filters['maxDate'])) {
            $maxDate = new \DateTime($filters['maxDate']);
            $maxDate->setTime(23, 59, 59); // Pour inclure toute la journée
            $qb->andWhere('e.date_debut <= :maxDate')
               ->setParameter('maxDate', $maxDate);
        }
    
        if (!empty($filters['eventType'])) {
            $qb->andWhere('e.typeevent = :eventType')
               ->setParameter('eventType', $filters['eventType']);
        }
    
        // Nouveau filtre par lieu (recherche partielle)
        if (!empty($filters['lieu'])) {
            $qb->andWhere('LOWER(e.lieu) LIKE LOWER(:lieu)')
               ->setParameter('lieu', '%' . $filters['lieu'] . '%');
        }
         if (!empty($filters['minDate'])) {
        $minDate = new \DateTime($filters['minDate']);
        $qb->andWhere('e.date_debut >= :minDate')
           ->setParameter('minDate', $minDate);
    }
    if (!empty($filters['minEndDate'])) {
        $minEndDate = new \DateTime($filters['minEndDate']);
        $qb->andWhere('e.date_fin >= :minEndDate')
           ->setParameter('minEndDate', $minEndDate);
    }
    if (!empty($filters['maxEndDate'])) {
        $maxEndDate = new \DateTime($filters['maxEndDate']);
        $maxEndDate->setTime(23, 59, 59); // Inclure toute la journée
        $qb->andWhere('e.date_fin <= :maxEndDate')
           ->setParameter('maxEndDate', $maxEndDate);
    }

        
        
    
        return $qb->getQuery()->getResult();
    }

    public function findAvailableEventTypes(): array
    {
        return $this->createQueryBuilder('e')
            ->select('DISTINCT e.typeevent')
            ->orderBy('e.typeevent', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }
    // Il est particulièrement utile lorsqu'on a besoin de construire des requêtes avec des conditions dynamiques, des jointures.
// le LEFT JOIN est utilisé pour s'assurer que tous les événements sont inclus sont inclus hata ken andou maandouch  trnasport ihotou null 
    public function findSortedEvents(array $reservedIds, array $filters): array
{
    $qb = $this->createQueryBuilder('e')
        ->leftJoin('e.transport', 't')
        ->addSelect('t');

    // Appliquer les filtres
    if (!empty($filters['minPrice'])) {
        $qb->andWhere('e.prix >= :minPrice')
           ->setParameter('minPrice', $filters['minPrice']);
    }

    if (!empty($filters['maxPrice'])) {
        $qb->andWhere('e.prix <= :maxPrice')
           ->setParameter('maxPrice', $filters['maxPrice']);
    }

    if (!empty($filters['eventType'])) {
        $qb->andWhere('e.typeevent = :eventType')
           ->setParameter('eventType', $filters['eventType']);
    }

    if (!empty($filters['lieu'])) {
        $qb->andWhere('LOWER(e.lieu) LIKE LOWER(:lieu)')
           ->setParameter('lieu', '%' . $filters['lieu'] . '%');
    }

    if (!empty($filters['minDate'])) {
        $qb->andWhere('e.date_debut >= :minDate')
           ->setParameter('minDate', new \DateTime($filters['minDate']));
    }

    if (!empty($filters['maxDate'])) {
        $qb->andWhere('e.date_debut <= :maxDate')
           ->setParameter('maxDate', new \DateTime($filters['maxDate']));
    }

    if (!empty($reservedIds)) {
         // Récupérer les types et lieux des événements réservés
        //crée une sous-requête qui sera utilisée pour récupérer les types et lieux des événements réservés,
        $subQb = $this->createQueryBuilder('e_reserved')// Filtre les événements réservés par leurs IDs
            ->select('DISTINCT e_reserved.typeevent, e_reserved.lieu')// Associe les IDs des événements réservés à la sous-requête
            ->where('e_reserved.id IN (:reservedIds)')
            ->setParameter('reservedIds', $reservedIds);
// Exécuter la sous-requête pour obtenir les préférences
        $reservedPreferences = $subQb->getQuery()->getResult();
 //si les preferences mawjoudin bich naamel tries bihom
        if (!empty($reservedPreferences)) {
            $types = array_column($reservedPreferences, 'typeevent');
            $lieux = array_column($reservedPreferences, 'lieu');

            // Trier les événements par priorité
            $qb->addSelect("
                CASE 
                    WHEN e.typeevent IN (:types) AND e.lieu IN (:lieux) THEN 1
                    WHEN e.typeevent IN (:types) THEN 2
                    WHEN e.lieu IN (:lieux) THEN 3
                    ELSE 4
                END AS HIDDEN priority
            ")
            ->setParameter('types', $types)
            ->setParameter('lieux', $lieux)
            ->orderBy('priority', 'ASC')
            ->addOrderBy('e.date_debut', 'ASC');
        }
    } else {
        $qb->orderBy('e.date_debut', 'ASC');
    }
//getQuery convertir le querybuilder enobjet query la requete est prete a execute
//getResukt pour executer la requete 
    return $qb->getQuery()->getResult();
}

  
}
    //    /**
    //     * @return Events[] Returns an array of Events objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('e.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Events
    //    {
    //        return $this->createQueryBuilder('e')
    //            ->andWhere('e.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

