<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class UpdateData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:update-data';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Updating user's  firstname, lastname and timezone";

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::all();
        $users->each(function ($user) {
            $user->update([
                'firstname' => fake()->firstName(),
                'lastname' => fake()->lastName(),
                'timezone' => fake()->timezone(),
            ]);
        });
    }
}
