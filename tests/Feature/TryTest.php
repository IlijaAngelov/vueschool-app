<?php

//Arrange, Act, Assert


use App\Models\User;

beforeEach(function () {
    User::factory()->create();
});

it('has users', function() {
//    sleep(3);
   $this->assertDatabaseHas('users', [
       'id' => 1
   ]);
});

it('has users 2', function() {
   $this->assertDatabaseHas('users', [
       'id' => 1
   ]);
});
