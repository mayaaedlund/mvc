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
        // Hämta kortleken från sessionen
        $deck = $session->get('deck', []);

        // Hämta spelarens hand från sessionen
        $playerHand = $session->get('playerHand');

        // Dra ett kort från kortleken
        $drawnCard = array_pop($deck);

        // Lägg till det dragna kortet i spelarens hand
        $playerHand->add(new CardGraphic($drawnCard));

        // Hämta poängen för det dragna kortet
        $pointsForDrawnCard = $cardPoints->getPoints($drawnCard);

        // Spara det dragna kortet i sessionen
        $drawnCards = $session->get('drawn_cards', []);
        $drawnCards[] = $drawnCard;
        $session->set('drawn_cards', $drawnCards);

        // Hämta befintliga poäng eller sätt till 0 om inga poäng finns
        $points = $session->get('points', 0);

        // Lägg till poängen för det dragna kortet till de befintliga poängen
        $points += $pointsForDrawnCard;

        // Om poängen överstiger 21, markera att spelaren har slutat och att spelet är över
        if ($points > 21) {
            $session->set('playerStopped', true);
            $session->set('gameOn', false);
            $this->addFlash('warning', 'Du gick över 21. Dealern vinner.');
        }

        // Spara de uppdaterade poängen i sessionen
        $session->set('points', $points);

        // Uppdatera kortleken i sessionen efter att ett kort har dragits
        $session->set('deck', $deck);
    }

    public function drawCardForDealer(SessionInterface $session, CardPoints $cardPoints): void
    {
        // Hämta kortleken från sessionen
        $deck = $session->get('deck', []);

        // Hämta dealerns hand från sessionen
        $dealerHand = $session->get('dealerHand', new CardHand());

        // Dra ett kort från kortleken
        $drawnCard = array_pop($deck);

        // Lägg till det dragna kortet i dealerns hand
        $dealerHand->add(new CardGraphic($drawnCard));

        // Hämta poängen för det dragna kortet
        $pointsForDrawnCard = $cardPoints->getPoints($drawnCard);

        // Spara det dragna kortet i sessionen
        $dealerCards = $session->get('dealer_cards', []);
        $dealerCards[] = $drawnCard;
        $session->set('dealer_cards', $dealerCards);

        // Hämta bankens poäng eller sätt till 0 om inga poäng finns
        $dealerPoints = $session->get('dealerpoints', 0);

        // Lägg till poängen för det dragna kortet till de befintliga poängen
        $dealerPoints += $pointsForDrawnCard;

        // Spara de uppdaterade poängen i sessionen
        $session->set('dealerpoints', $dealerPoints);

        // Uppdatera kortleken i sessionen efter att ett kort har dragits
        $session->set('deck', $deck);
    }

    public function evaluateWinner(SessionInterface $session, CardPoints $cardPoints): void
    {
        $playerPoints = $session->get('points', 0);
        $dealerPoints = $session->get('dealerpoints', 0);

        // Beräkna skillnaden mellan 21 och poängen för både spelaren och dealern
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




    ///
    /*



        public function hitPlayer(): void
        {
            // Ge ett kort till spelaren
            $this->playerHand->add($this->deck->drawCard());
        }

        public function hitDealer(): void
        {
            // Ge ett kort till dealern
            $this->dealerHand->add($this->deck->drawCard());
        }

        public function calculateWinner(): string
        {
            // Implementera logik för att beräkna vinnaren
            // Till exempel, jämför summan av spelarens hand med dealerns hand
            $playerPoints = $this->playerHand->calculatePoints();
            $dealerPoints = $this->dealerHand->calculatePoints();

            if ($playerPoints > 21) {
                return "Dealer wins, player busted.";
            } elseif ($dealerPoints > 21) {
                return "Player wins, dealer busted.";
            } elseif ($playerPoints > $dealerPoints) {
                return "Player wins.";
            } elseif ($playerPoints < $dealerPoints) {
                return "Dealer wins.";
            } else {
                return "It's a tie.";
            }
        }
        */
}
