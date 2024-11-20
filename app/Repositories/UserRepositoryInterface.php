<?php

namespace App\Repositories;

interface UserRepositoryInterface
{
    public function update(array $data);
    public function getApiUser($id);
    public function getDbUser($apiUser);
}
