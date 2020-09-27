<?php

namespace App\Commands;

use App\Support\Vault;

class VaultCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'vault';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'View the contents of your local vault';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        dd(Vault::load());
    }
}
