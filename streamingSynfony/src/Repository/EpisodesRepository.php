<?php

namespace App\Repository;

use App\Entity\Episodes;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Episodes|null find($id, $lockMode = null, $lockVersion = null)
 * @method Episodes|null findOneBy(array $criteria, array $orderBy = null)
 * @method Episodes[]    findAll()
 * @method Episodes[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EpisodesRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Episodes::class);
    }

    public function findAll() {
        return $this->createQueryBuilder('e')
            ->orderBy('e.titre', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findById($id) {
        return $this->createQueryBuilder('e')
            ->andWhere('e.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findBySaisonId($id) {
        return $this->createQueryBuilder('e')
            ->andWhere('e.saison = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
            ;
    }


    // /**
    //  * @return Episodes[] Returns an array of Episodes objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Episodes
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
