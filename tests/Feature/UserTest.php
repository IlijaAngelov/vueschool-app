<?php

use Illuminate\Support\Facades\Http;

test('api returns users list', function () {
    $response = $this->getJson((env('API_URL')));

    expect($response->status())->toBe(200);

});

test('api returns user data', function () {
//    $user = \App\Models\User::factory()->create();
    $response = Http::get((env('API_USER_URL') . 1));

    expect($response->status())->toBe(200);

    $jsonData = $response->json();

    // data from user id 1
    expect($jsonData)->toMatchArray([
        'firstName' => 'Emily',
        'lastName' => 'Johnson',
        'email' => 'emily.johnson@x.dummyjson.com'
//        'timezone' => 'America/Los_Angeles',
    ]);


});
