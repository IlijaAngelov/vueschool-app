<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Support\Facades\Http;

class UserRepository implements UserRepositoryInterface
{
    public function __construct(){}

    public function getApiUser($id): array
    {
        return HTTP::get((env('API_USER_URL') . '/' . $id))->json();
    }

    public function getDbUser($apiUser): User
    {
        return User::where('email', $apiUser['email'])->first();
    }

    public function update($apiUser)
    {
        if(is_numeric($apiUser)){
            $apiUser = $this->getApiUser($apiUser);
        }
        $dbUser = $this->getDbUser($apiUser);
        $dataToUpdate = [];
        // There isnt 'last' updated_at param in the dummy api, so we will hard code updated_at to be now()
        $apiUser['updated_at'] = now();
        if($apiUser['updated_at'] > $dbUser['updated_at']){
            if(isset($apiUser['firstName'])){
                $dataToUpdate['firstname'] = $apiUser['firstName'];
            }
            if(isset($apiUser['lastName'])){
                $dataToUpdate['lastname'] = $apiUser['lastName'];
            }
            if(isset($apiUser['address']['stateCode'])){
                $dataToUpdate['timezone'] = $apiUser['address']['stateCode'];
            }
            $dataToUpdate['is_synced'] = 1;
            $dataToUpdate['updated_at'] = now();
            User::where('email', $dbUser->email)->update($dataToUpdate);
        }
    }
}
