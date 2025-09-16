<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

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
        User::factory()->create();
    }
}
