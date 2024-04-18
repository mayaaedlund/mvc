<?php
namespace App\Card;

use App\Card\Card;

class CardHand
{
    private $hand = [];

    public function add(Card $card): void
    {
        $this->hand[] = $card;
    }

    // Metod för att lägga till alla kort i ordning i handen
    public function addOrderedCards(): void
    {
        for ($i = 1; $i <= 52; $i++) {
            $card = new CardGraphic($i); // Skapa kort med specifika värden
            $this->add($card);
        }
    }

    public function roll(): void
    {
        foreach ($this->hand as $card) {
            $card->roll();
        }
    }

    public function getNumberDices(): int
    {
        return count($this->hand);
    }

    public function getValues(): array
    {
        $values = [];
        foreach ($this->hand as $card) {
            $values[] = $card->getValue();
        }
        return $values;
    }

    public function getString(): array
    {
        $values = [];
        foreach ($this->hand as $card) {
            $values[] = $card->getAsString();
        }
        return $values;
    }
}