<?php

use App\Entity\User;
use App\Tests\ApiTester;
use Codeception\Util\HttpCode;

class QuotesApiControllerCest
{

    public function testNotAuthorizedRequest(ApiTester $I)
    {

        $I->wantToTest("No access  to quotes list without X-AUTH-TOKEN");
        $I->haveHttpHeader('Content-Type', 'application/json');
        $I->sendGET('/api/v1/quotes');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        $I->wantToTest("No access to creation of the quote  without X-AUTH-TOKEN");
        $I->sendPOST('/api/v1/quotes');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        $I->wantToTest("No access to edit of the quote  without X-AUTH-TOKEN");
        $I->sendPUT('/api/v1/quotes/5');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        $I->wantToTest("No access to deletion of the quote  without X-AUTH-TOKEN");
        $I->sendDELETE('/api/v1/quotes/5');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

        $I->wantToTest("No access to quote list by author  without X-AUTH-TOKEN");
        $I->sendGET('/api/v1/quotes/5/author');
        $I->seeResponseCodeIs(HttpCode::UNAUTHORIZED);

    }

    public function testQuotesList(ApiTester $I)
    {
        $I->grabUserAndPrepareAuthHeaders();
        $I->wantToTest(' empty list of quotes');
        $I->sendGET('/api/v1/quotes');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();


        $I->createQuotesByCurrentUser(5,'Author One');
        $I->wantToTest('not empty list of quotes');
        $I->sendGET('/api/v1/quotes');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->seeResponseIsJson();

        $I->seeResponseJsonMatchesXpath('//quotes/author/name');
        //$I->seeResponseMatchesJsonType(['id' => 'integer','content'=>'string','author'=>'array','name'=>'string'],"//quotes[1]");

    }

    public function testNotValidQuoteCreation(ApiTester $I)
    {
        $I->grabUserAndPrepareAuthHeaders();
        $I->wantToTest("creation of empty   quote");
        $I->sendPOST('/api/v1/quotes',['content'=>'','author'=>['name'=>'First Name Author']]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->wantToTest("creation of empty author");
        $I->sendPOST('/api/v1/quotes',['content'=>'This is a quote not a sandwich','author'=>['name'=>'']]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->wantToTest("creation quote with html tags prohibited");
        $I->sendPOST('/api/v1/quotes',['content'=>'This is a quote not a sandwich <img src="/xss.jph">','author'=>['name'=>'Author']]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->wantToTest("creation quote with html tags in author name is prohibited");
        $I->sendPOST('/api/v1/quotes',['content'=>'This is a quote not a sandwich','author'=>['name'=>'Author <img src="/portrait.jpg">']]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->wantToTest("creation too long quote  is prohibited");
        $I->sendPOST('/api/v1/quotes',['content'=>'
        This is a quote not a sandwich very long quote 
        .........................
        .......................
        .......................
        .......................
        .......................
        .......................
        .......................
        ','author'=>['name'=>'Author One']]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);

        $I->wantToTest("creation too long author name is prohibited");
        $I->sendPOST('/api/v1/quotes',['content'=>'
        This is a quote not a sandwich','author'=>['name'=>'
        very long author name 
        .........................
        .......................
        .......................
        .......................
        .......................
        .......................
        .......................']]);
        $I->seeResponseCodeIs(HttpCode::BAD_REQUEST);
    }

    public function testValidQuoteCreationEditDeletion(ApiTester $I)
    {
        $I->grabUserAndPrepareAuthHeaders();

        $I->wantToTest('quote creation');
        $quote = 'My exact quote is '.time();
        $I->sendPOST('/api/v1/quotes',['content'=>$quote,'author'=>['name'=>'Author One']]);
        $I->seeResponseCodeIs(HttpCode::CREATED);
        $location  = $I->grabHttpHeader('Location');
        $I->sendGET($location);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['content'=>$quote]);

        $I->wantToTest('edit current quote');
        $new_quote = 'My edit quote is '.time();
        $I->sendPUT($location,['content'=>$new_quote,'author'=>['name'=>'Author One']]);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->wantTo('check that edit was success');
        $I->sendGET($location);
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['content'=>$new_quote]);

        $I->wantToTest('Deletion of  current quote');
        $I->sendDELETE($location);
        $I->seeResponseCodeIs(HttpCode::NO_CONTENT);
        $I->wantTo('check that deletion was success');
        $I->sendGET($location);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);

    }

    public function checkByAuthorListing(ApiTester $I)
    {
        $user = $I->grabUserAndPrepareAuthHeaders();

        $I->createQuotesByCurrentUser(5,'Author One');
        $I->createQuotesByCurrentUser(5,'Author Two');
        $I->wantToTest('Quotes Listing by Author');
        $I->sendGET('/api/v1/quotes');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['name'=>'Author One']);
        $I->seeResponseContainsJson(['name'=>'Author Two']);
        $quotes = json_decode($I->grabResponse(),true);
        $firstQuoteId = $quotes['quotes'][0]['id'];

        $I->wantTo('get list of quotes by author');
        $I->sendGET('/api/v1/quotes/'.$firstQuoteId.'/author');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseIsJson();
        $I->seeResponseContainsJson(['name'=>'Author One']);
        $I->dontSeeResponseContainsJson(['name'=>'Author Two']);

    }

    public function testDifferentUsersQuotes(ApiTester $I)
    {
        $I->wantToTest('access to quotes by different users');

        $user1 = $I->grabUserAndPrepareAuthHeaders();

        $user1QuoteId = explode('/',$I->createQuote('User1MarkQuote','Author One'));
        $user1QuoteId = array_pop($user1QuoteId);

        $user2 = $I->grabUserAndPrepareAuthHeaders();

        $user2QuoteId = explode('/',$I->createQuote('User2MarkQuote','Author One'));
        $user2QuoteId = array_pop($user2QuoteId);

        $I->sendGET('/api/v1/quotes');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseIsJson();

        $I->seeResponseContainsJson(['content'=>'My exact quote is User2MarkQuote']);
        $I->dontSeeResponseContainsJson(['content'=>'My exact quote is User1MarkQuote']);

        $I->wantTo('grab other user quote ');
        $I->sendGET('/api/v1/quotes/'.$user1QuoteId);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);


        $I->prepareAuthHeaders($user1);

        $I->sendGET('/api/v1/quotes');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseIsJson();

        $I->seeResponseContainsJson(['content'=>'My exact quote is User1MarkQuote']);
        $I->dontSeeResponseContainsJson(['content'=>'My exact quote is User2MarkQuote']);

        $I->wantTo('grab other user quote ');
        $I->sendGET('/api/v1/quotes/'.$user2QuoteId);
        $I->seeResponseCodeIs(HttpCode::NOT_FOUND);
    }


    public function testRandomQuote(ApiTester $I)
    {
        $user = $I->grabUserAndPrepareAuthHeaders();
        $I->wantToTest('fetch random quote');
        $I->createQuotesByCurrentUser(15,'Author One');
        $I->sendGET('/api/v1/quotes/random');
        $I->seeResponseCodeIs(HttpCode::OK);
        $I->canSeeResponseIsJson();


    }

}
