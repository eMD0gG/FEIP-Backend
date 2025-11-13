<?php

namespace App\DTO;

class CreateHouseDto
{
    public function __construct(
        public float $area,
        public string $address,
        public int $price,
        public int $bedrooms,
        public int $distanceToSea,
        public bool $hasShower,
        public bool $hasBathroom,
    ) {
    }
}
