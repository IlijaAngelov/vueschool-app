<?php

namespace App\Service;

use App\Repositories\UserRepository;

class UserService
{
    public function __construct(
        protected UserRepository $userRepository
    ){}

    public function update($apiUser): void
    {
        $this->userRepository->update($apiUser);
    }
}
