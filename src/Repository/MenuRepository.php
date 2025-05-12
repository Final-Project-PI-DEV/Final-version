<?php

namespace App\Repository;

use App\Entity\Menu;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Menu>
 */
class MenuRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
    }

    //    /**
    //     * @return Menu[] Returns an array of Menu objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('m.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Menu
    //    {
    //        return $this->createQueryBuilder('m')
    //            ->andWhere('m.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
    /**
     * Recherche les restaurants proposant un plat spécifique.
     *
     * @param string $dishName Le nom du plat à rechercher.
     * @return array Les restaurants correspondants.
     */
    public function findRestaurantsByDishName(string $dishName): array
    {
        return $this->createQueryBuilder('m')
            ->andWhere('m.repas LIKE :dishName') // Recherche par le champ "repas"
            ->setParameter('dishName', '%' . $dishName . '%')
            ->join('m.restaurant', 'r') // Jointure avec l'entité Restaurant
            ->select('r.id, r.nomResto') // Sélectionne uniquement les champs nécessaires
            ->getQuery()
            ->getResult();
    }
}
