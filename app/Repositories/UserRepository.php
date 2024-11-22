<?php

namespace App\Repositories;

use App\DataTransferObjects\ApiUserDto;
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

    public function update($data)
    {
        if(is_numeric($data)){
            $data = $this->getApiUser($data);
        }
        $apiUser = new ApiUserDto($data);
        $dataApiUser = $apiUser->data;
        $dbUser = $this->getDbUser($data);
        $dataToUpdate = [];
        // There isnt 'last' updated_at param in the dummy api, so we will hard code updated_at to be now()
        $dataApiUser['updated_at'] = now();
        if($dataApiUser['updated_at'] > $dbUser['updated_at']){
            if(isset($dataApiUser['firstName'])){
                $dataToUpdate['firstname'] = $dataApiUser['firstName'];
            }
            if(isset($dataApiUser['lastName'])){
                $dataToUpdate['lastname'] = $dataApiUser['lastName'];
            }
            if(isset($dataApiUser['address']['stateCode'])){
                $dataToUpdate['timezone'] = $dataApiUser['address']['stateCode'];
            }
            $dataToUpdate['is_synced'] = 1;
            $dataToUpdate['updated_at'] = now();
            User::where('email', $dbUser->email)->update($dataToUpdate);
        }
    }
}
