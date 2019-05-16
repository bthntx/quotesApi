<?php

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Util\HttpCode;

class AuthApiControllerCest
{

    public function testRegistration(ApiTester $I)
    {
        $I->wantToTest("registration not working with method GET");
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/api/v1/register');
        $I->seeResponseCodeIs(HttpCode::METHOD_NOT_ALLOWED);

        $I->wantToTest("registration working with method POST");
        $I->sendPOST('/api/v1/register');

        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(['uuid' => 'string','api_token'=>'string','refresh_token'=>'string']);


    }


    public function testExpiredToken(ApiTester $I)
    {
        $I->wantToTest("get something with expired token");
        //$users =  $I->grabEntitiesFromRepository(User::class);
        $users =  $I->grabUsers();
        $I->assertGreaterOrEquals(1,count($users),'Should be at least 1 user');
        $user = $users[0];
        $expireDate = new \DateTime();
        $user->setTokenExpirationDate($expireDate);
        $I->flushToDatabase();

        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->haveHttpHeader('X-AUTH-TOKEN', $user->getApiToken());
        $I->sendGET('/api/v1/quotes');
        $I->seeResponseCodeIs(HttpCode::FORBIDDEN);

    }

    public  function testRefreshToken(ApiTester $I)
    {
        $user = $I->createUser();

        $I->wantToTest("refresh tokens with wrong pair api and refresh token");
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendPUT('/api/v1/refreshToken',['old_token'=>'','refresh_token'=>$user->getRefreshToken()]);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
        $I->sendPUT('/api/v1/refreshToken',['old_token'=>$user->getApiToken(),'refresh_token'=>'']);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

        $I->wantToTest("refresh tokens with correct pair api and refresh token");
        $I->sendPUT('/api/v1/refreshToken',['old_token'=>$user->getApiToken(),'refresh_token'=>$user->getRefreshToken()]);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();
        $I->seeResponseMatchesJsonType(['uuid' => 'string','api_token'=>'string','refresh_token'=>'string']);

    }

}
