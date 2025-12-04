<?php

namespace App\DTO;

class HouseDto
{
    public function __construct(
        public ?int $id = null,
        public ?float $area = null,
        public ?string $address = null,
        public ?int $price = null,
        public ?int $bedrooms = null,
        public ?int $distanceToSea = null,
        public ?bool $hasShower = null,
        public ?bool $hasBathroom = null,
    ) {
    }
}
