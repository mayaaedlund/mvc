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
    ): Response {
        // Skapa ett nytt deck och spara det i sessionen om det inte redan finns
        if (!$session->has('deck')) {
            $deck = $cardGraphic->getAllNumbers(); // Hämta alla kortnummer
            $session->set('deck', $deck);
        }

        return $this->redirectToRoute('card_draw');
    }


    #[Route("/card/draw/start", name: "card_draw", methods: ['GET'])]
    public function start(SessionInterface $session): Response
    {
        $deck = $session->get('deck', []);

        // Hämta det dragna kortet från sessionen om det finns
        $drawnCard = $session->get('drawn_card');

        $cardsLeft = count($deck);

        $data = [
            "deck" => $deck,
            "drawnCard" => $drawnCard,
            "cardsLeft" => $cardsLeft,
        ];

        return $this->render('card/play.html.twig', $data);
    }


    #[Route("/card/draw/add", name: "add_card", methods: ['POST'])]
    public function addCard(SessionInterface $session): Response
    {
        $deck = $session->get('deck', []);

        $randomKey = array_rand($deck);
        $drawnCard = $deck[$randomKey];

        unset($deck[$randomKey]);

        $session->set('deck', $deck);

        // Spara det dragna kortet direkt i sessionen
        $session->set('drawn_card', $drawnCard);

        return $this->redirectToRoute('card_draw');
    }

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