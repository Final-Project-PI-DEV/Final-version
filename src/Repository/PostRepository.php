<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Post>
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    //    /**
    //     * @return Post[] Returns an array of Post objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('p.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?Post
    //    {
    //        return $this->createQueryBuilder('p')
    //            ->andWhere('p.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

    // src/Repository/PostRepository.php
    public function findFilteredPosts(array $filters)
    {
        $qb = $this->createQueryBuilder('p');
    
        if (!empty($filters['search'])) {
            $qb->andWhere('LOWER(p.title) LIKE LOWER(:search) OR LOWER(p.content) LIKE LOWER(:search)')
               ->setParameter('search', '%' . $filters['search'] . '%');
        }
    
        if (!empty($filters['lieu'])) {
            $qb->andWhere('LOWER(p.lieu) LIKE LOWER(:lieu)')
               ->setParameter('lieu', '%' . $filters['lieu'] . '%');
        }
    
        if (!empty($filters['startDate'])) {
            $qb->andWhere('p.createdAt >= :startDate')
               ->setParameter('startDate', new \DateTime($filters['startDate']));
        }
    
        if (!empty($filters['endDate'])) {
            $qb->andWhere('p.createdAt <= :endDate')
               ->setParameter('endDate', new \DateTime($filters['endDate']));
        }
    
        return $qb->getQuery()->getResult();
    }
    
    
}
