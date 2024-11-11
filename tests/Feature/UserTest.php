<?php

// Arrange, Act, Assert(Expect)

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

// Modify/Create new/better version for updating the user data without fake, make an API call ( use the method from Controller )
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
