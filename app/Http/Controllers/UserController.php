<?php

namespace App\Http\Controllers;

use App\Jobs\SyncUsersJob;
use App\Models\User;
use App\Service\UserService;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function __construct(
        protected UserService $userService
    ){}

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

    public function getUser($id): array
    {
        return HTTP::get((env('API_USER_URL') . '/' . $id))->json();
    }

    public function update($apiUser): void
    {
        $this->userService->update($apiUser);
    }
}
