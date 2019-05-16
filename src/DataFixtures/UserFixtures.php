<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Ramsey\Uuid\Uuid;

class UserFixtures extends Fixture
{


    public function load(ObjectManager $manager)
    {
        // Expired User for testing
        $user = new User();
        $user->setUuid(Uuid::uuid4());
        $user->setApiToken('DEMOTOKENFORTESTING');
        $expireDate = new \DateTime();
        //$expireDate->add(new \DateInterval('P1D'));
        $user->setTokenExpirationDate($expireDate);
        $user->setRefreshToken('DEMOTOKENFORTESTINGCHANGE');
        $manager->persist($user);
        $manager->flush();
    }
}
