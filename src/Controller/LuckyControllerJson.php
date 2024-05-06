<?php

namespace App\Controller;

use App\Card\Card;
use App\Card\CardGraphic;
use App\Card\CardHand;
use App\Card\SortedCards;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;


use Symfony\Component\HttpFoundation\Session\SessionInterface;

class LuckyControllerJson
{
    #[Route("/api", name: "init")]
    public function init(): JsonResponse
    {
        $quotes = [
            "Välkommen till api-sidorna.
            På api/deck ser du alla kort i ordning.
            Api/deck/shuffle ser du alla kort i shufflad ordning.
            "
        ];

        $data = [
            'quote' => $quotes,
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/lucky/number")]
    public function jsonNumber(): Response
    {
        $number = random_int(0, 100);

        $data = [
            'lucky-number' => $number,
            'lucky-message' => 'Hi there!',
        ];

        // return new JsonResponse($data);

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/quote", name: "api_quote")]
    public function getQuote(): JsonResponse
    {
        $quotes = [
            "Old ways don't open new doors.",
            "It will all make sense eventually.",
            "Lagom är sämst."
        ];

        $randomIndex = array_rand($quotes);
        $quote = $quotes[$randomIndex];
        $timestamp = time();

        $formattedTimestamp = date('Y-m-d H:i:s', $timestamp);

        $data = [
            'quote' => $quote,
            'date' => date('Y-m-d'),
            'formatted_timestamp' => $formattedTimestamp
        ];

        $response = new JsonResponse($data);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck", name: "api_deck")]
    public function deck(): Response
    {
        // Skapa en instans av CardGraphic för att hämta kortdata
        $card = new CardGraphic();
        $numbers = $card->getAllCards();

        // Skapa en JsonResponse med kortdatan
        $response = new JsonResponse($numbers);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/shuffle", name: "api_shuffle")]
    public function shuffle_deck(): Response
    {
        // Starta eller återuppta sessionen
        session_start();

        // Skapa en instans av CardGraphic för att hämta kortdata
        $card = new CardGraphic();
        $numbers = $card->shuffledCards();

        // Spara kortdatan i sessionen
        $_SESSION['shuffled_cards'] = $numbers;

        // Skapa en JsonResponse med kortdatan
        $response = new JsonResponse($numbers);
        $response->setEncodingOptions(
            $response->getEncodingOptions() | JSON_PRETTY_PRINT
        );
        return $response;
    }

    #[Route("/api/deck/draw", name: "api_draw")]
    public function drawCard(Request $request): Response
    {
        // Starta eller återuppta sessionen
        session_start();

        // Kontrollera om det finns en kortlek i sessionen, om inte, skapa en ny
        if (!isset($_SESSION['shuffled_cards']) || empty($_SESSION['shuffled_cards'])) {
            // Skapa en instans av CardGraphic för att skapa en ny kortlek
            $card = new CardGraphic();
            $_SESSION['shuffled_cards'] = $card->shuffledCards();
            $_SESSION['drawn_cards'] = []; // Skapa en tom array för att lagra dragna kort
        }

        // Dra ett kort från kortleken
        $drawnCard = array_shift($_SESSION['shuffled_cards']);

        // Lägg till det dragna kortet till listan med dragna kort
        $_SESSION['drawn_cards'][] = $drawnCard;

        // Skapa JSON-struktur med alla dragna kort och antalet kvarvarande kort i kortleken
        $response = [
            'drawn_cards' => $_SESSION['drawn_cards'],
            'remaining_cards' => count($_SESSION['shuffled_cards'])
        ];

        // Returnera JSON-svar
        return new JsonResponse($response);
    }


    #[Route("/api/deck/draw/{number}", name: "draw_number", methods: ['POST', 'GET'])]
    public function drawCards(Request $request, int $number): Response
    {
        // Starta eller återuppta sessionen
        session_start();

        // Kontrollera om det finns en kortlek i sessionen, om inte, skapa en ny
        if (!isset($_SESSION['shuffled_cards']) || empty($_SESSION['shuffled_cards'])) {
            // Skapa en instans av CardGraphic för att skapa en ny kortlek
            $card = new CardGraphic();
            $_SESSION['shuffled_cards'] = $card->shuffledCards();
            $_SESSION['drawn_cards'] = []; // Skapa en tom array för att lagra dragna kort
        }

        // Dra det önskade antalet kort från kortleken
        $drawnCards = array_splice($_SESSION['shuffled_cards'], 0, $number);

        // Lägg till de dragna korten till listan med dragna kort
        $_SESSION['drawn_cards'] = array_merge($_SESSION['drawn_cards'], $drawnCards);

        // Skapa JSON-struktur med alla dragna kort och antalet kvarvarande kort i kortleken
        $response = [
            'drawn_cards' => $_SESSION['drawn_cards'],
            'remaining_cards' => count($_SESSION['shuffled_cards'])
        ];

        // Returnera JSON-svar
        return new JsonResponse($response);
    }

}
