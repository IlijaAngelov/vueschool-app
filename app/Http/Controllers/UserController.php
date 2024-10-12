<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function show(){
        $response = Http::get('https://quizapi.io/api/v1/questions', [
            'apiKey' => env('API_KEY'),
//            'limit' => 10
        ])->json();

        User::create([
            'firstname' => $response['firstname'],
            'lastname' => $response['lastname'],
            'email' => $response['email'],
            'timezone' => $response['address']['stateCode'],
            'is_synced' => 1,
        ]);

    }

    public function users(){
        $responses = Http::get('https://dummyjson.com/users', [
        ])->json();
        foreach($responses['users'] as $response) {
            User::create([
                'firstname' => $response['firstName'],
                'lastname' => $response['lastName'],
                'email' => $response['email'],
                'password' => Str::password(12),
                'timezone' => $response['address']['stateCode'],
                'is_synced' => 0,
            ]);
        }
    }

    public function individuals(){
        $response = Http::get('https://dummyjson.com/users', [
            'limit' => 10
        ]);
        return $response->json();
    }

    public function user(){

        $data = Http::get('https://dummyjson.com/users/' . rand(1, 100) )->json();

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
}
