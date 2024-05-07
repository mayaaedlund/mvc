<?php

namespace App\Controller;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\SortedCards;
use App\Card\CardPoints; 

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardGameController extends AbstractController
{
    private $cardHand;

    public function __construct(CardHand $cardHand)
    {
        $this->cardHand = $cardHand;
    }

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
            $hand->add(new CardGraphic($drawnCard));
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

        // Hämta alla tidigare dragna kort och lägg till det nya dragna kortet i arrayen
        $drawnCards = $session->get('drawn_cards', []);
        $drawnCards[] = $drawnCard;
        $session->set('drawn_cards', $drawnCards);

        return $this->redirectToRoute('card_draw');
    }


    #[Route("/card/deck/draw/{num<\d+>}", name: "draw_multiple")]
    public function draw_multiple(int $num, SessionInterface $session, CardGraphic $cardGraphic): Response
    {
        // Skapa ett nytt deck och spara det i sessionen om det inte redan finns
        if (!$session->has('deck')) {
            $deck = $cardGraphic->getAllNumbers(); // Hämta alla kortnummer
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


        // Hämta alla tidigare dragna kort eller skapa en ny array om det är första dragningen
        $drawnCards = $session->get('drawn_cards', []);

        // Dra det angivna antalet kort från kortleken och lägg till dem i den dragna kort-arrayen
        for ($i = 0; $i < $num; $i++) {
            if (empty($deck)) {
                break;
            }

            $randomKey = array_rand($deck);
            $drawnCard = $deck[$randomKey];
            unset($deck[$randomKey]);
            $drawnCards[] = $drawnCard;

        }

        // Uppdatera sessionen med den nya kortleken och den dragna kort-arrayen
        $session->set('deck', $deck);
        $session->set('drawn_cards', $drawnCards);

        // Räkna antalet kort kvar i kortleken
        $cardsLeft = count($deck);

        // Skapa en tom array för att lagra symboler för alla dragna kort
        $cardStrings = [];

        // Loopa igenom alla tidigare dragna kort och hämta deras symboler
        foreach ($drawnCards as $drawnCard) {
            $cardStrings[] = $cardGraphic->getGraphic($drawnCard);
        }

        // Skicka med all information till mallen för att visas
        $data = [
            "deck" => $deck,
            "drawnCards" => $drawnCards, // Skicka med alla tidigare dragna kort
            "cardsLeft" => $cardsLeft,
            "cardStrings" => $cardStrings, // Skicka med symboler för alla tidigare dragna kort
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
        // Hämta handen från sessionen
        $hand = $session->get('hand');

        // Om handen inte finns i sessionen, skapa en ny tom hand
        if (!$hand instanceof CardHand) {
            $hand = new CardHand();
            $session->set('hand', $hand);
        }

        //bankens hand
        $bankhand = $session->get('bankhand');

        // Om handen inte finns i sessionen, skapa en ny tom hand
        if (!$bankhand instanceof CardHand) {
            $bankhand = new CardHand();
            $session->set('bankhand', $bankhand);
        }


        // Hämta kortleken från sessionen
        $deck = $session->get('deck', []);

        $cardGraphic = new CardGraphic();

        // Om det inte finns någon kortlek i sessionen, skapa en och blanda den
        if (empty($deck)) {
            $cardGraphic = new CardGraphic();
            $deck = $cardGraphic->getAllNumbers();
            shuffle($deck);
            $session->set('deck', $deck);
        }

        $points = $session->get('points');

        if ($points === null) {
            // Om 'points' inte finns i sessionen, skapa den och sätt till 0
            $session->set('points', 0);
            $points = 0; // Sätt även $points till 0
        }

        $bankpoints = $session->get('bankpoints');

        if ($bankpoints === null) {
            // Om 'points' inte finns i sessionen, skapa den och sätt till 0
            $session->set('bankpoints', 0);
            $bankpoints = 0; // Sätt även $points till 0
        }

        //hämta alla kort banken dragit
        $bankCards = $session->get('bank_cards', []);

        $cardSymbols = [];

        // Loopa igenom alla tidigare dragna kort och hämta deras symboler
        foreach ($bankCards as $bankCard) {
            $cardSymbols[] = $cardGraphic->getGraphic($bankCard);
        }

        // Hämta alla tidigare dragna kort från sessionen
        $drawnCards = $session->get('drawn_cards', []);

        // Räkna antalet kort kvar i kortleken
        $cardsLeft = count($deck);

        // Skapa en tom array för att lagra symboler för alla dragna kort
        $cardStrings = [];

        // Loopa igenom alla tidigare dragna kort och hämta deras symboler
        foreach ($drawnCards as $drawnCard) {
            $cardStrings[] = $cardGraphic->getGraphic($drawnCard);
        }

        $playerStopped = $session->get('playerStopped', false);

        $gameOn = $session->get('gameOn', true);

        // Skicka alla relevanta data till vyn för rendering
        $data = [
            "deck" => $deck,
            "bankCards" => $bankCards,
            "bankpoints" => $bankpoints,
            "drawnCards" => $drawnCards, // Skicka med alla tidigare dragna kort
            "bankCards" => $bankCards,
            "cardSymbols" => $cardSymbols,
            "cardsLeft" => $cardsLeft,
            "cardStrings" => $cardStrings, // Skicka med symboler för alla tidigare dragna kort
            "points" => $points, // Skicka med poängen
            "playerStopped" => $playerStopped,
            "gameOn" => $gameOn
        ];

        return $this->render('card/gameplan.html.twig', $data);
    }


    #[Route("/game/player", name: "play_card", methods: ['POST'])]
    public function playCard(SessionInterface $session, CardPoints $cardPoints): Response
    {
        // Hämta kortleken från sessionen
        $deck = $session->get('deck', []);

        $hand = $session->get('hand');

        // Dra ett kort från kortleken
        $drawnCard = array_pop($deck);

        $hand->add(new CardGraphic($drawnCard));


        $pointsForDrawnCard = $cardPoints->getPoints($drawnCard);

        // Spara det dragna kortet i sessionen
        $drawnCards = $session->get('drawn_cards', []);
        $drawnCards[] = $drawnCard;
        $session->set('drawn_cards', $drawnCards);

        // Hämta befintliga poäng eller sätt till 0 om inga poäng finns
        $points = $session->get('points', 0);

        // Lägg till poängen för det dragna kortet till de befintliga poängen
        $points += $pointsForDrawnCard;

        if ($points > 21) {
            $this->addFlash(
                'warning',
                'DU FÖRLORADE',
            );

            $session->set('playerStopped', true);
            $session->set('gameOn', false);
        }

        // Spara de uppdaterade poängen i sessionen
        $session->set('points', $points);

        // Uppdatera kortleken i sessionen efter att ett kort har dragits
        $session->set('deck', $deck);

        // Omdirigera användaren tillbaka till spelet (eller vart du vill skicka dem)
        return $this->redirectToRoute('gamestart');
    }

    #[Route("/game/dealer", name: "dealer_card", methods: ['POST'])]
    public function dealerCard(SessionInterface $session, CardPoints $cardPoints): Response
    {
        // Hämta kortleken från sessionen
        $deck = $session->get('deck', []);

        // Hämta bankens hand från sessionen
        // Hämta bankens hand från sessionen
        $bankhand = $session->get('bankhand');
    
        // Dra ett kort från kortleken
        $bankCard = array_pop($deck);


        $bankhand->add(new CardGraphic($bankCard));

        $pointsForDrawnCard = $cardPoints->getPoints($bankCard);

        // Spara det dragna kortet i sessionen
        $bankCards = $session->get('bank_cards', []);
        $bankCards[] = $bankCard;
        $session->set('bank_cards', $bankCards);

        $bankpoints = $session->get('bankpoints', 0);

        $bankpoints += $pointsForDrawnCard;

        // Spara de uppdaterade poängen i sessionen
        $session->set('bankpoints', $bankpoints);

        // Uppdatera kortleken i sessionen efter att ett kort har dragits
        $session->set('deck', $deck);

        if ($bankpoints > 16) {
            $session->set('gameOn', false);
            return $this->redirectToRoute('dealer_stay');
        } else { 

            if ($bankpoints > 21) {
                $this->addFlash(
                    'warning',
                    'DU VINNER!',
                );

                $session->set('gameOn', false);
            }

            // Omdirigera användaren tillbaka till spelet
            return $this->redirectToRoute('gamestart');
        }
    }

    #[Route("/game/dealer/stay", name: "dealer_stay", methods: ['POST', 'GET'])]
    public function dealerStay(SessionInterface $session, CardPoints $cardPoints): Response
    {
        $points = $session->get('points', 0);
        $bankpoints = $session->get('bankpoints', 0);

        // Beräkna skillnaden mellan 21 och poängen för både användaren och banken
        $userDifference = abs(21 - $points);
        $bankDifference = abs(21 - $bankpoints);

        if ($points > 21 && $bankpoints > 21) {
            $this->addFlash('warning', 'Både du och banken gick över 21. Ingen vinner.');
        } elseif ($points > 21) {
            $this->addFlash('warning', 'Du gick över 21. Banken vinner.');
        } elseif ($bankpoints > 21) {
            $this->addFlash('warning', 'Banken gick över 21. Du vinner.');
        } else {
            // Välj vinnaren baserat på den som är närmast 21
            if ($userDifference < $bankDifference) {
                $this->addFlash('warning', 'Du är närmast 21. Du vinner.');
            } elseif ($userDifference > $bankDifference) {
                $this->addFlash('warning', 'Banken är närmast 21. Banken vinner.');
            } else {
                $this->addFlash('warning', 'Det blev lika, ingen vinner.');
            }

            $session->set('gameOn', false);
        }

        return $this->redirectToRoute('gamestart');
    }


    #[Route("/game/player/stay", name: "player_stay", methods: ['POST'])]
    public function playerStay(SessionInterface $session, CardPoints $cardPoints): Response
    {
        $points = $session->get('points', 0);

        if ($points > 0) {
            $this->addFlash(
                'warning',
                'BANKEN TUR',
            );

            $session->set('playerStopped', true);
        }

        return $this->redirectToRoute('gamestart');
    }

    #[Route("/reset-game", name: "reset_game", methods: ['POST'])]
    public function resetGame(SessionInterface $session): Response
    {
        // Rensa all sessionens data
        $session->clear();

        // Återvänd till en annan sida eller visa en bekräftelse
        return $this->redirectToRoute('gamestart');
    }




}

