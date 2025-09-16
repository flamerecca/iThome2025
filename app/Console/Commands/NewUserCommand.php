<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class NewUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:new-user-command';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add New User';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $password = Str::random(10);
        $user = User::factory()->create([
            'password' => Hash::make($password)
        ]);
        $this->info('name: ' . $user->name);
        $this->info('password: ' . $password);
    }
}
