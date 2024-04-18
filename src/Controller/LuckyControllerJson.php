<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class LuckyControllerJson
{
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

}
