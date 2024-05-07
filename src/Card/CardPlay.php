<?php

namespace App\Card;

use App\Card\Card;
use App\Card\CardHand;
use App\Card\CardGraphic;
use App\Card\CardPoints;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class CardPlay extends AbstractController
{
    private $deck = [];
    private $playerHand = [];
    private $dealerHand = [];

    public function __construct()
    {

        $this->playerHand = new CardHand();
        $this->dealerHand = new CardHand();
    }


    public function getPlayerHand(): CardHand
    {
        return $this->playerHand;
    }

    public function getDealerHand(): CardHand
    {
        return $this->dealerHand;
    }

    public function drawCardForPlayer(SessionInterface $session, CardPoints $cardPoints): void
    {
        $deck = $session->get('deck', []);

        $playerHand = $session->get('playerHand');

        $drawnCard = array_pop($deck);

        $playerHand->add(new CardGraphic($drawnCard));

        $pointsForDrawnCard = $cardPoints->getPoints($drawnCard);

        $drawnCards = $session->get('drawn_cards', []);
        $drawnCards[] = $drawnCard;
        $session->set('drawn_cards', $drawnCards);

        $points = $session->get('points', 0);

        $points += $pointsForDrawnCard;

        if ($points > 21) {
            $session->set('playerStopped', true);
            $session->set('gameOn', false);
            $this->addFlash('warning', 'Du gick över 21. Dealern vinner.');
        }

        $session->set('points', $points);

        $session->set('deck', $deck);
    }

    public function drawCardForDealer(SessionInterface $session, CardPoints $cardPoints): void
    {
        $deck = $session->get('deck', []);

        $dealerHand = $session->get('dealerHand', new CardHand());

        $drawnCard = array_pop($deck);

        $dealerHand->add(new CardGraphic($drawnCard));

        $pointsForDrawnCard = $cardPoints->getPoints($drawnCard);

        $dealerCards = $session->get('dealer_cards', []);
        $dealerCards[] = $drawnCard;
        $session->set('dealer_cards', $dealerCards);

        $dealerPoints = $session->get('dealerpoints', 0);

        $dealerPoints += $pointsForDrawnCard;

        $session->set('dealerpoints', $dealerPoints);

        $session->set('deck', $deck);
    }

    public function evaluateWinner(SessionInterface $session, CardPoints $cardPoints): void
    {
        $playerPoints = $session->get('points', 0);
        $dealerPoints = $session->get('dealerpoints', 0);

        $playerDifference = abs(21 - $playerPoints);
        $dealerDifference = abs(21 - $dealerPoints);

        if ($playerPoints > 21 && $dealerPoints > 21) {
            $this->addFlash('warning', 'Både du och dealern gick över 21. Ingen vinner.');
        } elseif ($playerPoints > 21) {
            $this->addFlash('warning', 'Du gick över 21. Dealern vinner.');
        } elseif ($dealerPoints > 21) {
            $this->addFlash('warning', 'Dealern gick över 21. Du vinner.');
        } else {
            // Välj vinnaren baserat på den som är närmast 21
            if ($playerDifference < $dealerDifference) {
                $this->addFlash('warning', 'Du är närmast 21. Du vinner.');
            } elseif ($playerDifference > $dealerDifference) {
                $this->addFlash('warning', 'Dealern är närmast 21. Dealern vinner.');
            } else {
                $this->addFlash('warning', 'Det blev lika, ingen vinner.');
            }

            $session->set('gameOn', false);
        }
    }

    public function playerStay(SessionInterface $session, CardPoints $cardPoints): void
    {
        $points = $session->get('points', 0);

        if ($points > 0) {
            $this->addFlash(
                'warning',
                'Nu är det dealerns tur!',
            );

            $session->set('playerStopped', true);
        }
    }
}
