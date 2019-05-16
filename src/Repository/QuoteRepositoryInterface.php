<?php

namespace App\Repository;


use App\Entity\Quote;

interface QuoteRepositoryInterface
{
    public function  getRandomQuote():?Quote;
}
