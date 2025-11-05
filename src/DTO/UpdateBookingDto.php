<?php

namespace App\DTO;

class UpdateBookingDto
{
    public function __construct(
        public ?string $status = null,
        public ?string $comment = null
    ) {}
}