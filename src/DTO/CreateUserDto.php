<?php

namespace App\DTO;

class CreateUserDto
{
    public function __construct(
        public string $name,
        public string $number,
    ) {
    }
}
