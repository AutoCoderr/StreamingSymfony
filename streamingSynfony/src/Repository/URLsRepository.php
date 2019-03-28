<?php

namespace App\Repository;

use App\Entity\URLs;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method URLs|null find($id, $lockMode = null, $lockVersion = null)
 * @method URLs|null findOneBy(array $criteria, array $orderBy = null)
 * @method URLs[]    findAll()
 * @method URLs[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class URLsRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, URLs::class);
    }

    public function findByFilmId($id) {
        return $this->createQueryBuilder('u')
            ->andWhere('u.Film = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    public function findByEpisodeId($id) {
        return $this->createQueryBuilder('u')
            ->andWhere('u.Episode = :val')
            ->setParameter('val', $id)
            ->getQuery()
            ->getResult()
            ;
    }

    // /**
    //  * @return URLs[] Returns an array of URLs objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?URLs
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
