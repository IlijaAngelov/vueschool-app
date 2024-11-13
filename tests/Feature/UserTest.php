<?php

// Arrange, Act, Assert(Expect)

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use Illuminate\Testing\Fluent\AssertableJson;

test('api returns users list', function () {
    $response = $this->getJson((env('API_URL')));

    expect($response->status())->toBe(200);

});

test('an authenticated user gets the correct status code', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertStatus(200);
});

test('the response comes in a standard format', function () {
    $user = \App\Models\User::factory()->create();
    $response = Http::get((env('API_USER_URL') . $user->id));

    expect($response->status())->toBe(200);

    $data = $response->json();
    AssertableJson::fromArray($data)
        ->has('email')
        ->etc();
});

test('the functionality of the getUser method', function () {
    $controller = new UserController();

    $response = $controller->getApiUser(1);
    expect($response)->toMatchArray([
        'firstName' => "Emily",
        'email' => "emily.johnson@x.dummyjson.com"
    ]);
});

test('updates user with newer data', function () {
    $apiUser = User::factory()->create([
        'id' => 1,
        'firstname' => 'Eme',
        'lastname' => 'Johnson',
        'email' => 'emily.johnson@x.dummyjson.com',
        'timezone' => 'America/Los_Angeles',
        'is_synced' => 1,
        'updated_at' => now()
    ]);

    $controller = new UserController();
    // rabote, samo ako se iskomentirani tii dva metoda u update metodo, ama i treba istio metod da gi prima 2ta parametri
    $controller->update($apiUser);

    expect(User::latest()->first())
        ->firstname->toBe('Eme')
        ->lastname->toBe('Johnson')
        ->timezone->toBe('America/Los_Angeles')
        ->is_synced->toBe(1)
        ->updated_at->diffInSeconds(now())->toBeLessThan(2);

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

    expect(User::latest()->first())
        ->firstname->toBe('Newfirstname')
        ->lastname->toBe('NewLastName')
        ->timezone->toBe('GMT+1')
        ->is_synced->toBe(1)
        ->updated_at->diffInSeconds(now())->toBeLessThan(2);

});

test('rate limiting', function () {

})->todo();

test('jobs in queue', function () {

})->todo();
