<?php

namespace App\Repository;

use App\Entity\Saisons;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Saisons|null find($id, $lockMode = null, $lockVersion = null)
 * @method Saisons|null findOneBy(array $criteria, array $orderBy = null)
 * @method Saisons[]    findAll()
 * @method Saisons[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SaisonsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Saisons::class);
    }

    public function findAll() {
        return $this->createQueryBuilder('s')
            ->orderBy('s.nom', 'ASC')
            ->getQuery()
            ->getResult()
            ;
    }

    public function findBySerieId($id) {
        return $this->createQueryBuilder('s')
            ->andWhere('s.serie = :val')
            ->orderBy('s.nom', 'ASC')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findById($id) {
        return $this->createQueryBuilder('s')
            ->andWhere('s.id = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return Saisons[] Returns an array of Saisons objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Saisons
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
