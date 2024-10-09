<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Http;

class UserController extends Controller
{
    public function show(){
        $response = Http::get('https://quizapi.io/api/v1/questions', [
            'apiKey' => env('API_KEY'),
            'limit' => 10
        ]);

        return $response->json();
    }

    public function users(){
        $response = Http::get('https://dummyjson.com/users', [
            'limit' => 10
        ]);
        return $response->json();
    }

    public function individuals(){
        $response = Http::get('https://dummyjson.com/users', [
            'limit' => 10
        ]);
        return $response->json();
    }

    public function user(){
        return Http::get('https://dummyjson.com/users/' . rand(1, 100) )->json();
    }
}
