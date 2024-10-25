<?php

// Arrange, Act, Assert(Expect)

use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request as Request;

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

test('update user data from an external API', function () {

    $oldUser = User::factory()->create([
        'id' => 1,
        'firstname' => 'Emily',
        'lastname' => 'Johnson',
        'email' => 'emily.johnson@x.dummyjson.com',
        'timezone' => 'America/Los_Angeles',
        'updated_at' => now()->subDays(5)
    ]);
    // Mocks the API response
    Http::fake([
        env('API_URL') . '/1' => Http::response([
            'firstname' => 'Newfirstname',
            'lastname' => 'NewLastName',
            'timezone' => 'GMT+1',
            'updated_at' => now()->toDateTimeString()
        ]),
    ]);

    $apiData = Http::get(env('API_URL') . '/1')->json();
    $dataToUpdate = [
        'firstname' => $apiData['firstname'],
        'lastname' => $apiData['lastname'],
        'timezone' => $apiData['timezone'],
        'is_synced' => 1,
        'updated_at' => $apiData['updated_at'],
    ];
    User::where('id', $oldUser->id)->update($dataToUpdate);

    $updatedUser = User::find(1);
    expect($updatedUser->firstname)->toBe('Newfirstname')
        ->and($updatedUser->lastname)->toBe('NewLastName')
        ->and($updatedUser->timezone)->toBe('GMT+1')
        ->and($updatedUser->updated_at->diffInSeconds(now()))->toBeLessThan(2); // Allows a 2-second margin for `updated_at`

});
