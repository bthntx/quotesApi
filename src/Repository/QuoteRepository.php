<?php

namespace App\Repository;

use App\Entity\Quote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Quote|null find($id, $lockMode = null, $lockVersion = null)
 * @method Quote|null findOneBy(array $criteria, array $orderBy = null)
 * @method Quote[]    findAll()
 * @method Quote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteRepository extends ServiceEntityRepository implements QuoteRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        //@TODO inject UserId and apply filter
        parent::__construct($registry, Quote::class);
    }


    public function getRandomQuote():?Quote
    {
        // We can use  ORDER BY RAND() but it will be slow on large tables.

        $count = $this->createQueryBuilder('cnt')->select('COUNT(cnt)')->getQuery()->getSingleScalarResult();
        $rand = rand(1,$count)-1;
        return $this->createQueryBuilder('q')
            ->setFirstResult($rand)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }



}
