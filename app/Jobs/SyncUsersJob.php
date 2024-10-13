<?php

namespace App\Jobs;

use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use function Laravel\Prompts\error;

class SyncUsersJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, Dispatchable, SerializesModels;

    public $tries = 3;

    protected $users;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
//        // for single user
//        try {
//            $data = Http::get((env('API_URL') . '/' . rand(1, 100)))->json();
//            $user = User::where('email', $data['email'])->first();
//            User::updateOrCreate([
//                'email' => $data['email']],
//                [
//                    'firstname' => $data['firstName'],
//                    'lastname' => $data['lastName'],
//                    'password' => Str::password(12),
//                    'timezone' => $data['address']['stateCode'],
//                    'is_synced' => 1,
//                ]);
//        }catch (Throwable $e) {
//                report($e);
//                throw new \Exception('Failed!');
//        }

        // bulk users -- modify chunks and limit
        try {
            $users = User::where('is_synced', 0)->limit(10)->get();

            // check if there are not updated users, else abort
            $users->chunk(5)->each(function ($chunkedUsers) {
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
        }catch (Throwable $e) {
                report($e);
                throw new \Exception('Failed!');
        }
    }

    public function failed(\Throwable $exception)
    {
        error('This job has failed!');
    }
}
