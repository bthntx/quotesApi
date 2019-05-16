<?php


namespace App\Filter;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class FilterConfigurator
{
    protected $em;
    protected $tokenStorage;

    public function __construct(ObjectManager $em, TokenStorageInterface $tokenStorage)
    {
        $this->em              = $em;
        $this->tokenStorage    = $tokenStorage;
    }

    public function onKernelRequest()
    {
            if ($user = $this->getUser()) {
                $filter = $this->em->getFilters()->enable('quote_user_filter');
                $filter->setParameter('id', $user->getId());
            }
    }

    private function getUser()
    {
        $token = $this->tokenStorage->getToken();
        if (!$token) {
            return null;
        }
        $user = $token->getUser();
        if (!($user instanceof UserInterface)) {
            return null;
        }
        return $user;
    }
}
