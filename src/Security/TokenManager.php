<?php


namespace App\Security;

use App\Repository\UserRepository;
use App\Repository\UserRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use http\Exception\InvalidArgumentException;
use mysql_xdevapi\Exception;
use Ramsey\Uuid\Uuid;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class TokenManager implements TokenManagerInterface
{

    public const TOKEN_TYPE_UUID = 'uuid';
    public const TOKEN_TYPE_AUTH = 'apiToken';
    public const TOKEN_TYPE_CHANGE = 'refreshToken';

    protected static $tokenTypes = [self::TOKEN_TYPE_UUID, self::TOKEN_TYPE_AUTH, self::TOKEN_TYPE_CHANGE];

    /** @var EntityManagerInterface */
    protected $em;
    /** @var UserRepository */
    protected $userRepo;
    /** @var UserPasswordEncoderInterface */
    protected $encoder;

    /**
     * TokenManager constructor.
     * @param EntityManagerInterface $em
     * @param UserRepositoryInterface $userRepo
     */
    public function __construct(EntityManagerInterface $em, UserRepositoryInterface $userRepo)
    {
        $this->em = $em;
        $this->userRepo = $userRepo;
    }


    public function getUserByToken($token)
    {

        return $this->userRepo->findOneBy(['apiToken' => $token]);
    }


    public function checkAuthToken($token)
    {
           return $this->isTokenNotExpired($token);
    }

    public function isTokenNotExpired($token)
    {
        //@TODO bind apiToken to some client's fingerprint
        $result = $this->userRepo->findNonExpiredToken($token);

        return $result !== null;
    }



    public function changeAuthTokens(string $oldToken, string $refreshToken)
    {
        //@TODO bcrypt refreshToken - it shouldn't be stored as plain text
        $user = $this->userRepo->findOneBy(['apiToken' => $oldToken, 'refreshToken' => $refreshToken]);
        if (!$user) {
            throw new NotFoundHttpException('no such tokens');
        }
        $user->setRefreshToken($this->generateToken(TokenManager::TOKEN_TYPE_CHANGE));
        $user->setApiToken($this->generateToken(TokenManager::TOKEN_TYPE_AUTH));
        $expireDate = new \DateTime();
        $expireDate->add(new \DateInterval('P1D'));
        $user->setTokenExpirationDate($expireDate);
        $this->em->flush();
        return $user;

    }

    public function generateToken($type)
    {
        if (!in_array($type, TokenManager::$tokenTypes)) {
            throw new \InvalidArgumentException('Incorrect Token Type '.$type);
        }

        do {
            $newToken = Uuid::uuid4();
            $_existingToken = $this->userRepo->findOneBy([$type => $newToken]);
        } while (null !== $_existingToken);

        return $newToken;


    }


}
