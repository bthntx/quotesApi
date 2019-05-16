<?php

namespace App\Repository;

use App\Entity\QuoteAuthor;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method QuoteAuthor|null find($id, $lockMode = null, $lockVersion = null)
 * @method QuoteAuthor|null findOneBy(array $criteria, array $orderBy = null)
 * @method QuoteAuthor[]    findAll()
 * @method QuoteAuthor[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class QuoteAuthorRepository extends ServiceEntityRepository implements  QuoteAuthorRepositoryInterface
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, QuoteAuthor::class);
    }

}
