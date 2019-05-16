<?php

namespace App\Tests;

use App\Entity\User;
use Codeception\Util\HttpCode;
use Ramsey\Uuid\Uuid;

/**
 * Inherited Methods
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(PHPMD)
 */
class ApiTester extends \Codeception\Actor
{
    use _generated\ApiTesterActions;

    /**
     * Define custom actions here
     */



    public function grabUserAndPrepareAuthHeaders()
    {

        $user = $this->createUser();
        $this->prepareAuthHeaders($user);

        return $user;
    }

    public function prepareAuthHeaders(User $user)
    {
        $this->haveHttpHeader('Content-Type', 'application/json');
        $this->haveHttpHeader('X-AUTH-TOKEN', $user->getApiToken());

    }

    public function grabUsers($countUsers = 1)
    {
        $this->wantTo('grab or create users for test');
        $users = $this->grabEntitiesFromRepository(User::class);
        $count = count($users);
        if ($count < $countUsers) {
            for ($i = $count; $i < $countUsers; $i++) {
                $this->createUser();
            }
            $users = $this->grabEntitiesFromRepository(User::class);
        }

        return $users;
    }

    public function grabAuth()
    {

    }

    public function createUser()
    {
        $expireDate = new \DateTime();
        $expireDate->add(new \DateInterval('P1D'));
        $userData = [
            'uuid' => 'testUuid'.time().Uuid::uuid4(),
            'apiToken' => 'testApiToken'.time().Uuid::uuid4(),
            'refreshToken' => 'testRefreshToken'.time().Uuid::uuid4(),
            'tokenExpirationDate' => $expireDate,
        ];
        $id = $this->haveInRepository(User::class, $userData);
        $user = $this->grabEntityFromRepository(User::class, ['id' => $id]);
        return $user;
    }


    public function createQuotesByCurrentUser($quotesNumber, $quthorName)
    {
        for ($i = 0; $i < $quotesNumber; $i++) {
            $this->createQuote($i, $quthorName);
        }
    }

    public function createQuote($name = '1', $author = 'Author One')
    {
        $quote = 'My exact quote is '.$name;
        $this->sendPOST('/api/v1/quotes', ['content' => $quote, 'author' => ['name' => $author]]);
        $this->seeResponseCodeIs(HttpCode::CREATED);
        return $this->grabHttpHeader('Location');
    }

}
