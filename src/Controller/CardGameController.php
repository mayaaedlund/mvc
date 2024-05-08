<?php

namespace App\Controller;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\SortedCards;
use App\Card\CardPoints;
use App\Card\CardPlay;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardGameController extends AbstractController
{
    /*private $cardHand;

    public function __construct(CardHand $cardHand)
    {
        $this->cardHand = $cardHand;
    }*/

    #[Route("/card", name: "card")]
    public function home(): Response
    {
        return $this->render('card/home.html.twig');
    }

    #[Route("/api", name: "api")]
    public function api(): Response
    {
        return $this->render('card/api.html.twig');
    }

    #[Route("/session", name: "session")]
    public function session(SessionInterface $session): Response
    {
        $data = [
            'session' => $session->all()
        ];
        return $this->render('session.html.twig', $data);
    }

    #[Route("/reset-session", name: "reset_session", methods: ['POST'])]
    public function resetSession(SessionInterface $session): Response
    {
        // Rensa all sessionens data
        $session->clear();

        // Återvänd till en annan sida eller visa en bekräftelse
        return $this->redirectToRoute('session');
    }

    #[Route("/card/draw/test", name: "draw_card")]
    public function testCard(): Response
    {
        $card = new CardGraphic();

        // Ta ett kort och få dess representation
        $card->take();
        $cardString = $card->getAsString();

        // Skicka data till mallen
        $data = [
            "cardString" => $cardString,
        ];

        // Rendera mallen och skicka med data
        return $this->render('card/one_card.html.twig', $data);
    }

    #[Route("/card/deck", name: "deck")]
    public function deckCard(): Response
    {
        $card = new CardGraphic();

        //
        $numbers = $card->getAllCards();

        // Skicka korten till mallen
        // Skicka data till mallen
        $data = [
            "cards" => $numbers,
        ];

        // Rendera mallen och skicka med data
        return $this->render('card/all_cards.html.twig', $data);
    }

    #[Route("/card/deck/shuffle", name: "shuffle_deck")]
    public function ShuffleCard(): Response
    {
        $card = new CardGraphic();

        //
        $numbers = $card->shuffledCards();

        // Skicka korten till mallen
        // Skicka data till mallen
        $data = [
            "cards" => $numbers,
        ];

        // Rendera mallen och skicka med data
        return $this->render('card/shuffled_cards.html.twig', $data);
    }


    #[Route("/card/init", name: "card_init_get", methods: ['GET'])]
    public function init(): Response
    {
        return $this->render('card/init.html.twig');
    }

    #[Route("/card/init", name: "card_init_post", methods: ['POST'])]
    public function initCallback(
        Request $request,
        SessionInterface $session,
        CardGraphic $cardGraphic
    ): Response {
        // Skapa ett nytt deck och spara det i sessionen om det inte redan finns
        if (!$session->has('deck')) {
            $deck = $cardGraphic->getAllNumbers(); // Hämta alla kortnummer
            $session->set('deck', $deck);
        }

        $hand = new CardHand();

        // Sparar den tomma handen i sessionen
        $session->set('hand', $hand);

        return $this->redirectToRoute('card_draw');
    }


    #[Route("/card/deck/draw", name: "card_draw", methods: ['GET'])]
    public function start(SessionInterface $session): Response
    {
        // Hämta handen från sessionen
        $hand = $session->get('hand');

        // Om handen inte finns i sessionen, skapa en ny tom hand
        if (!$hand instanceof CardHand) {
            $hand = new CardHand();
            $session->set('hand', $hand);
        }

        // Hämta kortleken från sessionen
        $deck = $session->get('deck', []);

        // Om det inte finns någon kortlek i sessionen, skapa en och blanda den
        if (empty($deck)) {
            $cardGraphic = new CardGraphic();
            $deck = $cardGraphic->getAllNumbers();
            shuffle($deck);
            $session->set('deck', $deck);
        }

        // Dra ett kort från kortleken till handen om handen är tom
        if ($hand->getNumberDices() === 0) {
            $drawnCard = array_pop($deck);
            $hand->add(new CardGraphic());
        }

        $drawnCards = $session->get('drawn_cards', []); // Hämta alla tidigare dragna kort
        $cardsLeft = count($deck);

        // Skapa en tom array för att lagra symboler för alla dragna kort
        $cardStrings = [];
        $cardGraphic = new CardGraphic();

        // Loopa igenom alla tidigare dragna kort och hämta deras symboler
        foreach ($drawnCards as $drawnCard) {
            $cardStrings[] = $cardGraphic->getGraphic($drawnCard);
        }

        $data = [
            "deck" => $deck,
            "drawnCards" => $drawnCards, // Skicka med alla tidigare dragna kort
            "cardsLeft" => $cardsLeft,
            "cardStrings" => $cardStrings, // Skicka med symboler för alla tidigare dragna kort
        ];

        return $this->render('card/play.html.twig', $data);
    }


    #[Route("/card/draw/add", name: "add_card", methods: ['POST'])]
    public function addCard(SessionInterface $session): Response
    {
        $deck = $session->get('deck', []);

        if (count($deck) < 1) {
            $this->addFlash(
                'warning',
                'Not enough cards in deck'
            );
            return $this->redirectToRoute('card_draw');
        }

        $randomKey = array_rand($deck);
        $drawnCard = $deck[$randomKey];

        unset($deck[$randomKey]);

        $session->set('deck', $deck);

        $drawnCards = $session->get('drawn_cards', []);
        $drawnCards[] = $drawnCard;
        $session->set('drawn_cards', $drawnCards);

        return $this->redirectToRoute('card_draw');
    }


    #[Route("/card/deck/draw/{num<\d+>}", name: "draw_multiple")]
    public function draw_multiple(int $num, SessionInterface $session, CardGraphic $cardGraphic): Response
    {
        if (!$session->has('deck')) {
            $deck = $cardGraphic->getAllNumbers();
            $session->set('deck', $deck);
        }

        $deck = $session->get('deck', []);

        if (count($deck) < $num) {
            $this->addFlash(
                'warning',
                'Not enough cards in deck'
            );
        }

        if ($num > 52) {
            throw new \Exception("Can not draw more than 52 cards!");
        }

        $drawnCards = $session->get('drawn_cards', []);

        for ($i = 0; $i < $num; $i++) {
            if (empty($deck)) {
                break;
            }

            $randomKey = array_rand($deck);
            $drawnCard = $deck[$randomKey];
            unset($deck[$randomKey]);
            $drawnCards[] = $drawnCard;

        }

        $session->set('deck', $deck);
        $session->set('drawn_cards', $drawnCards);

        // Räkna antalet kort kvar i kortleken
        $cardsLeft = count($deck);

        $cardStrings = [];

        foreach ($drawnCards as $drawnCard) {
            $cardStrings[] = $cardGraphic->getGraphic($drawnCard);
        }

        $data = [
            "deck" => $deck,
            "drawnCards" => $drawnCards,
            "cardsLeft" => $cardsLeft,
            "cardStrings" => $cardStrings,
        ];

        return $this->render('card/multiple.html.twig', $data);
    }



    #[Route("/game", name: "game", methods: ['GET'])]
    public function game(): Response
    {
        return $this->render('card/game.html.twig');
    }

    #[Route("/game/start", name: "gamestart", methods: ['GET'])]
    public function gamestart(SessionInterface $session, CardPoints $cardPoints): Response
    {

        $game = new CardPlay();
        //
        $playerHand = $game->getPlayerHand();
        $dealerHand = $game->getDealerHand();

        $session->set('playerHand', $playerHand);
        $session->set('dealerHand', $dealerHand);


        $deck = $session->get('deck', []);
        $cardGraphic = new CardGraphic();

        if (empty($deck)) {
            $cardGraphic = new CardGraphic();
            $deck = $cardGraphic->getAllNumbers();
            shuffle($deck);
            $session->set('deck', $deck);
        }

        $points = $session->get('points', 0);
        $dealerpoints = $session->get('dealerpoints', 0);

        $dealerCards = $session->get('dealer_cards', []);

        $cardSymbols = [];
        foreach ($dealerCards as $dealerCard) {
            $cardSymbols[] = $cardGraphic->getGraphic($dealerCard);
        }

        $drawnCards = $session->get('drawn_cards', []);

        $cardStrings = [];
        foreach ($drawnCards as $drawnCard) {
            $cardStrings[] = $cardGraphic->getGraphic($drawnCard);
        }

        $playerStopped = $session->get('playerStopped', false);
        $gameOn = $session->get('gameOn', true);

        $data = [
            "deck" => $deck,
            "dealerCards" => $dealerCards,
            "dealerpoints" => $dealerpoints,
            "drawnCards" => $drawnCards,
            "cardSymbols" => $cardSymbols,
            "cardStrings" => $cardStrings,
            "points" => $points,
            "playerStopped" => $playerStopped,
            "gameOn" => $gameOn
        ];

        return $this->render('card/gameplan.html.twig', $data);
    }


    #[Route("/game/player", name: "play_card", methods: ['POST', 'GET'])]
    public function playCard(SessionInterface $session, CardPoints $cardPoints, CardPlay $cardPlay): Response
    {
        $cardPlay->drawCardForPlayer($session, $cardPoints);

        return $this->redirectToRoute('gamestart');
    }

    #[Route("/game/dealer", name: "dealer_card", methods: ['POST', 'GET'])]
    public function dealerCard(SessionInterface $session, CardPoints $cardPoints, CardPlay $cardPlay): Response
    {
        $cardPlay->drawCardForDealer($session, $cardPoints);


        $dealerPoints = $session->get('dealerpoints');
        if ($dealerPoints > 16) {
            $session->set('gameOn', false);
            return $this->redirectToRoute('dealer_stay');
        } else {
            return $this->redirectToRoute('gamestart');
        }

    }


    #[Route("/game/dealer/stay", name: "dealer_stay", methods: ['POST', 'GET'])]
    public function dealerStay(SessionInterface $session, CardPoints $cardPoints, CardPlay $cardPlay): Response
    {
        $cardPlay->evaluateWinner($session, $cardPoints);

        return $this->redirectToRoute('gamestart');
    }

    #[Route("/game/player/stay", name: "player_stay", methods: ['POST', 'GET'])]
    public function playerStay(SessionInterface $session, CardPoints $cardPoints, CardPlay $cardPlay): Response
    {
        $cardPlay->playerStay($session, $cardPoints);

        return $this->redirectToRoute('gamestart');
    }


    #[Route("/reset-game", name: "reset_game", methods: ['POST'])]
    public function resetGame(SessionInterface $session): Response
    {
        $session->clear();

        return $this->redirectToRoute('gamestart');
    }

    #[Route("/game/doc", name: "gamedoc", methods: ['GET'])]
    public function gamedock(SessionInterface $session): Response
    {

        return $this->render('card/gamedocs.html.twig');
    }

    #[Route("/api/game", name: "api_game", methods: ['POST', 'GET'])]
    public function apigame(SessionInterface $session): Response
    {
        $points = $session->get('points', 0);
        $dealerpoints = $session->get('dealerpoints', 0);

        $response = [
            'points' => $points,
            'dealerpoints' => $dealerpoints,

        ];

        return new JsonResponse($response);
    }




}
