<?php

namespace App\Controller;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\SortedCards;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CardGameController extends AbstractController
{
    #[Route("/card", name: "card")]
    public function home(): Response
    {
        return $this->render('card/home.html.twig');
    }

    #[Route("/card/draw", name: "draw_card")]
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
    ): Response
    {
        $allCards = $cardGraphic->getAllCards();

        $cardsLeft = count($allCards);

        $session->set("cardsLeft", $cardsLeft);
        $session->set("drawnCards", 0);

        return $this->redirectToRoute('card_draw');
    }

    #[Route("/card/draw/start", name: "card_draw", methods: ['GET'])]
    public function start(
        SessionInterface $session
    ): Response
    {
        $card = new CardGraphic();

        $drawnCards = $session->get("drawnCards", []);

        $cardsLeft = $session->get("cardsLeft");

        $data = [
            "cardsLeft" => $session->get("cardsLeft"),
            "drawnCards" => $drawnCards, // Use correct variable name
            "cardString" => $session->get("cardString")
        ];

        return $this->render('card/play.html.twig', $data);
    }

    #[Route("/card/draw/add", name: "add_card", methods: ['POST'])]
    public function addCard(SessionInterface $session): Response
    {
        $drawnCards = $session->get('drawnCards', []);

        //Dra ett nytt kort 
        $card = new CardGraphic();
        $drawnCardValue = $card->take();
        $cardString = $card->getAsString(); 

        $session->set('drawnCards', $drawnCards);

        // Hämta och uppdatera data från sessionen (om det behövs)
        $cardsLeft = $session->get("cardsLeft");

        // Omdirigera tillbaka till draw-sidan
        return $this->redirectToRoute('card_draw');
    }
/*
    #[Route("/card/draw", name: "draw_card")]
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
*/
    

    /* Kod som funkar =
    #[Route("/card/draw/add", name: "add_card", methods: ['POST'])]
    public function addCard(SessionInterface $session): Response
    {
        // Skapa en instans av CardGraphic
        $card = new CardGraphic();
        
        $drawnCards = $session->get("drawnCards", []);

        // Hämta och uppdatera data från sessionen
        $cardsLeft = $session->get("cardsLeft");
        $drawnCards = $session->get("drawnCards");

        // Implementera logik för att lägga till kort här

        // Omdirigera tillbaka till draw-sidan
        return $this->redirectToRoute('card_draw');
    }

    */


/*
    #[Route("/card/game/draw", name: "card_draw", methods: ['POST'])]
    public function draw_card(SessionInterface $session): Response
    {
        // Hämta de dragna korten från sessionen
        $drawnCards = $session->get("drawn_cards", []);

        // Skapa en instans av CardGraphic
        $card = new CardGraphic();

        // Dra ett kort
        $card->take();
        $cardString = $card->getAsString();

        // Kolla om det dragna kortet redan finns i de dragna korten
        while (in_array($cardString, $drawnCards)) {
            // Dra ett nytt kort
            $card->take();
            $cardString = $card->getAsString();
        }

        // Lägg till det dragna kortet i de dragna korten
        $drawnCards[] = $cardString;

        // Uppdatera sessionen med de dragna korten
        $session->set("drawn_cards", $drawnCards);

        // Hämta antalet kort kvar i leken från sessionen
        $cardsLeft = count($card->getAllCards()) - count($drawnCards);

        // Skicka data till mallen
        $data = [
            "drawnCards" => $drawnCards,
            "cardsLeft" => $cardsLeft,
        ];

        // Rendera mallen och skicka med data
        return $this->render('card/draw_card.html.twig', $data);
    }


    






*/



    #[Route("/game/pig/test/roll/{num<\d+>}", name: "test_roll_num_dices")]
    public function testRollDices(int $num): Response
    {
        if ($num > 99) {
            throw new \Exception("Can not roll more than 99 dices!");
        }

        $diceRoll = [];
        for ($i = 1; $i <= $num; $i++) {
            // $die = new Dice();
            $die = new DiceGraphic();
            $die->roll();
            $diceRoll[] = $die->getAsString();
        }

        $data = [
            "num_dices" => count($diceRoll),
            "diceRoll" => $diceRoll,
        ];

        return $this->render('pig/test/roll_many.html.twig', $data);
    }

    #[Route("/game/pig/test/dicehand/{num<\d+>}", name: "test_dicehand")]
    public function testDiceHand(int $num): Response
    {
        if ($num > 99) {
            throw new \Exception("Can not roll more than 99 dices!");
        }

        $hand = new DiceHand();
        for ($i = 1; $i <= $num; $i++) {
            if ($i % 2 === 1) {
                $hand->add(new DiceGraphic());
            } else {
                $hand->add(new Dice());
            }
        }

        $hand->roll();

        $data = [
            "num_dices" => $hand->getNumberDices(),
            "diceRoll" => $hand->getString(),
        ];

        return $this->render('pig/test/dicehand.html.twig', $data);
    }




    #[Route("/game/pig/play", name: "pig_play", methods: ['GET'])]
    public function play(
        SessionInterface $session
    ): Response
    {
        $dicehand = $session->get("pig_dicehand");

        $data = [
            "pigDices" => $session->get("pig_dices"),
            "pigRound" => $session->get("pig_round"),
            "pigTotal" => $session->get("pig_total"),
            "diceValues" => $dicehand->getString()
        ];

        return $this->render('pig/play.html.twig', $data);
    }

    #[Route("/game/pig/roll", name: "pig_roll", methods: ['POST'])]
    public function roll(
        SessionInterface $session
    ): Response
    {
        $hand = $session->get("pig_dicehand");
        $hand->roll();

        $roundTotal = $session->get("pig_round");
        $round = 0;
        $values = $hand->getValues();
        foreach ($values as $value) {
            if ($value === 1) {
                $round = 0;
                $roundTotal = 0;

                $this->addFlash(
                    'warning',
                    'You got a 1 and you lost the round points!'
                );

                break;
            }
            $round += $value;
        }

        $session->set("pig_round", $roundTotal + $round);
        
        return $this->redirectToRoute('pig_play');
    }

    #[Route("/game/pig/save", name: "pig_save", methods: ['POST'])]
    public function save(
        SessionInterface $session
    ): Response
    {
        $roundTotal = $session->get("pig_round");
        $gameTotal = $session->get("pig_total");

        $session->set("pig_round", 0);
        $session->set("pig_total", $roundTotal + $gameTotal);

        $this->addFlash(
            'notice',
            'Your round was saved to the total!'
        );

        return $this->redirectToRoute('pig_play');
    }
}
