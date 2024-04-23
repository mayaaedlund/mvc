<?php

namespace App\Card;

class Card
{
    protected $value;

    public function __construct()
    {
        $this->value = null;
    }

    public function take(): int
    {
        $this->value = random_int(1, 52);
        return $this->value;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getAsString(): string
    {
        return "[{$this->value}]";
    }

    public function getAllNumbers(): array
    {
        $numbers = range(1, 52); // Skapar en array med alla tal från 1 till 52
        return $numbers;
    }


}
