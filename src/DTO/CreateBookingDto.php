<?php

namespace App\DTO;

class CreateBookingDto
{
    public function __construct(
        public ?string $comment = null,
    ) {
    }
}
