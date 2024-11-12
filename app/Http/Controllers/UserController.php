<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUsersJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Str;

class UserController extends Controller
{

    // Get users in batch
    public function users(): void{
        // modify limit
        $users = User::where('is_synced', 0)->limit(10)->get();

        // check if there are not updated users, else abort
        $users->chunk(100)->each(function ($chunkedUsers) {
           foreach($chunkedUsers as $user){
               $response = Http::get((env('API_USER_URL') . '/' . $user->id));

               if($response->successful()){
                   $data = $response->json();

                   $user->update([
                       'firstname' => $data['firstName'],
                       'lastname' => $data['lastName'],
                       'timezone' => $data['address']['stateCode'],
                       'is_synced' => 1,
                   ]);
               } else {
                   Log::error('Failed to sync user ' . $user->id);
               }
           }
        });
    }


// Getting users from open API for testing --- belongs to method above - users()
//        $responses = Http::get((env('API_URL')), [
//        ])->json();
//        foreach($responses['users'] as $response) {
//            User::create([
//                'firstname' => $response['firstName'],
//                'lastname' => $response['lastName'],
//                'email' => $response['email'],
//                'password' => Str::password(12),
//                'timezone' => $response['address']['stateCode'],
//                'is_synced' => 0,
//            ]);
//        }

    // Single User Get and Update methods
    public function getUser($id): array
    {
        return HTTP::get((env('API_USER_URL') . '/' . $id))->json();
    }

    public function update($user): void
    {
        $apiUser = $this->getUser($user);
        $dbUser = User::where('email', $apiUser['email'])->first();
        $dataToUpdate = [];

        // There isnt 'last' updated_at param in the dummy api, so we will hard code updated_at to be now()
        $apiUser['updated_at'] = now();
        if($apiUser['updated_at'] > $dbUser['updated_at']){
            if(isset($apiUser['firstName'])){
                $dataToUpdate['firstname'] = $apiUser['firstName'];
            }
//            $dataToUpdate['firstname'] = 'Eme';
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
