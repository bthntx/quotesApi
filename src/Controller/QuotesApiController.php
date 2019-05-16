<?php

namespace App\Controller;


use App\Entity\Quote;
use App\Form\QuoteType;
use App\Repository\QuoteRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


class QuotesApiController extends AbstractFOSRestController
{
    /** @var EntityManagerInterface $em */
    private $em;
    /** @var QuoteRepositoryInterface $quotesRepo */
    private $quotesRepository;


    /**
     * QuotesApiController constructor.
     * @param EntityManagerInterface $em
     * @param QuoteRepositoryInterface $quotesRepository
     */
    public function __construct(
        EntityManagerInterface $em,
        QuoteRepositoryInterface $quotesRepository
    ) {
        $this->em = $em;
        $this->quotesRepository = $quotesRepository;
    }


    public function index(Request $request): Response
    {
        //@TODO pagination and per page splitting

        $quotes = $this->quotesRepository->findAll();
        if (!$quotes) {
            return $this->handleView($this->view(['quotes'=>[]], Response::HTTP_OK));
        }

        return $this->handleView($this->view(['quotes'=>$quotes], Response::HTTP_OK));
    }

    public function getQuote(Request $request, int $id): Response
    {
        $quote = $this->getQuoteEntity($id);

        if (!$quote) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($quote, Response::HTTP_OK));
    }



    public function updateQuote(Request $request, int $id): Response
    {
        $quote = $this->getQuoteEntity($id);
        if (!$quote) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }

        return $this->processQuote($request, $quote);
    }



    protected function getQuoteEntity(int $id): ?Quote
    {
        return $this->quotesRepository->findOneBy(["id" => $id]);
    }




    protected function processQuote(Request $request, Quote $quote, bool $new = false): Response
    {

        $form = $this->createForm(QuoteType::class, $quote);
        $data = json_decode($request->getContent(), true);
        $form->submit($data);
        if ($form->isSubmitted() && $form->isValid() && $this->getUser()) {
            $quote->setUser($this->getUser());
            $this->em->persist($quote);
            $this->em->flush();
            if ($new) {
                return $this->handleView($this->view([], Response::HTTP_CREATED,
                    ['Location' => $this->generateUrl("api_rest_v1_quotes_get", ['id' => $quote->getId()])]));
            } else {
                return $this->handleView($this->view([], Response::HTTP_NO_CONTENT));
            }


        }

        return $this->handleView($this->view($form->getErrors(),Response::HTTP_BAD_REQUEST));

    }




    public function createQuote(Request $request): Response
    {

        $quote = new Quote();

        return $this->processQuote($request, $quote, true);
    }

    public function deleteQuote(Request $request, int $id)
    {
        $quote = $this->getQuoteEntity($id);
        if (!$quote) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }
        $this->em->remove($quote);
        $this->em->flush();
        //@TODO something with orphaned Authors when no other users quotes left

        return $this->handleView($this->view([], Response::HTTP_NO_CONTENT));
    }




    public function randomQuote(Request $request): Response
    {
        $quote = $this->quotesRepository->getRandomQuote();
        if (!$quote) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view($quote, Response::HTTP_OK));
    }



    public function authorQuotes(Request $request, int $quoteId): Response
    {
        $quote = $this->getQuoteEntity($quoteId);
        if (!$quote) {
            return $this->handleView($this->view(null, Response::HTTP_NOT_FOUND));
        }

        return $this->handleView($this->view(['quotes'=>$quote->getAuthor()->getQuotes()], Response::HTTP_OK));

    }

}
