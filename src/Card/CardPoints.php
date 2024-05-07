<?php

namespace App\Card;

class CardPoints extends Card
{
    private $points = [
        1 => 1,
        2 => 2,
        3 => 3,
        4 => 4,
        5 => 5,
        6 => 6,
        7 => 7,
        8 => 8,
        9 => 9,
        10 => 10,
        11 => 11,
        12 => 12,
        13 => 13,
        14 => 1,
        15 => 2,
        16 => 3,
        17 => 4,
        18 => 5,
        19 => 6,
        20 => 7,
        21 => 8,
        22 => 9,
        23 => 10,
        24 => 11,
        25 => 12,
        26 => 13,
        27 => 1,
        28 => 2,
        29 => 3,
        30 => 4,
        31 => 5,
        32 => 6,
        33 => 7,
        34 => 8,
        35 => 9,
        36 => 10,
        37 => 11,
        38 => 12,
        39 => 13,
        40 => 1,
        41 => 2,
        42 => 3,
        43 => 4,
        44 => 5,
        45 => 6,
        46 => 7,
        47 => 8,
        48 => 9,
        49 => 10,
        50 => 11,
        51 => 12,
        52 => 13,
    ];


    public function getPoints(int $cardValue): int
    {
        return $this->points[$cardValue];
    }
}
