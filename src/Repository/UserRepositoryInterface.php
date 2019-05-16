<?php

namespace App\Repository;


use App\Entity\Quote;

interface UserRepositoryInterface
{
    public function findNonExpiredToken($token);
}
