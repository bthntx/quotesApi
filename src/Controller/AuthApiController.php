<?php

namespace App\Controller;


use App\Entity\User;
use App\Security\TokenManager;
use App\Security\TokenManagerInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Ramsey\Uuid\Uuid;
use function Sodium\add;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class AuthApiController extends AbstractFOSRestController
{
    /** @var TokenManagerInterface */
    protected $tokenManager;
    /** @var EntityManagerInterface */
    protected $em;

    /**
     * AuthApiController constructor.
     * @param TokenManagerInterface $tokenManager
     * @param EntityManagerInterface $em
     */
    public function __construct(TokenManagerInterface $tokenManager, EntityManagerInterface $em)
    {
        $this->tokenManager = $tokenManager;
        $this->em = $em;
    }


    public function registerUser(Request $request): Response
    {


        if ($this->getUser()) {
            return $this->handleView($this->view(['message' => 'already registered'], Response::HTTP_FORBIDDEN));
        }

        //@TODO move to separate User Manager
        try {
            $user = new User();
            $user->setUuid($this->tokenManager->generateToken(TokenManager::TOKEN_TYPE_UUID));
            $user->setApiToken($this->tokenManager->generateToken(TokenManager::TOKEN_TYPE_AUTH));
            $expireDate = new \DateTime();
            $expireDate->add(new \DateInterval('P1D'));
            $user->setTokenExpirationDate($expireDate);
            $user->setRefreshToken($this->tokenManager->generateToken(TokenManager::TOKEN_TYPE_CHANGE));
            $this->em->persist($user);
            $this->em->flush();

            //Generate and return new both tokens
            return $this->handleView($this->view($user, Response::HTTP_OK));
        } catch (\Exception $e) {
            return $this->handleView($this->view($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR));
        }
    }

    public function changeToken(Request $request): Response
    {

        $data = json_decode($request->getContent(), true);
        if (!$data || !array_key_exists("old_token", $data) || !array_key_exists("refresh_token", $data)) {
            return $this->handleView($this->view([ 'message' => 'wrong parameters'], Response::HTTP_BAD_REQUEST));
        }
        $refreshedData = $this->tokenManager->changeAuthTokens($data['old_token'], $data['refresh_token']);

        return $this->handleView($this->view($refreshedData, Response::HTTP_OK));

    }

}
