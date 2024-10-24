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

    public function users(){
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



// Getting users from open API for testing
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
    }
    public function updateUser(){

        $data = Http::get((env('API_URL') . '/' . rand(1, 100)))->json();
        $user = User::where('email', $data['email'])->first();
        User::updateOrCreate([
            'email' => $data['email']],
            [
            'firstname' => $data['firstName'],
            'lastname' => $data['lastName'],
            'password' => Str::password(12),
            'timezone' => $data['address']['stateCode'],
            'is_synced' => 1,
        ]);
    }

    public function checkForUserUpdate(Request $request){
        $user = User::findOrFail($request->id);

        // Making new APi request for the user
        $data = Http::get((env('API_URL') . '/' . $user->id))->json();
        // Check if user has been updated, then conitnue to save the data, else abort
        $dataToUpdate = [];
        $data['updated_at'] = now();
        if($data['updated_at'] > $user['updated_at']){
            if(isset($data['firstName'])){
                $dataToUpdate['firstname'] = $data['firstName'];
            }
            if(isset($data['lastName'])){
                $dataToUpdate['lastname'] = $data['lastName'];
            }
            if(isset($data['address']['stateCode'])){
                $dataToUpdate['timezone'] = $data['address']['stateCode'];
            }
            $dataToUpdate['is_synced'] = 1;
            $dataToUpdate['updated_at'] = now();

            User::where('email', $user->email)->update($dataToUpdate);
        }
    }
}
