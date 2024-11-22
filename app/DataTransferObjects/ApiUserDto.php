<?php

namespace App\DataTransferObjects;

final readonly class ApiUserDto
{
    public function __construct(
        public readonly array $data,
    ){}
}
