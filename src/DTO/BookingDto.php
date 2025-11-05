<?php

namespace App\DTO;

class BookingDto
{
    public function __construct(
        public ?int $id,
        public ?int $userId,
        public ?int $houseId,
        public string $status,
        public ?string $comment
    ) {}
}