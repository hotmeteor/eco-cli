<?php

namespace App\Commands;

use App\Concerns\AsksForRepository;
use App\Support\Vault;

class RepoSwitchCommand extends Command
{
    use AsksForRepository;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'repo:switch
                            {--name= : The repo name (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Switch to a different repo context.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if (empty($this->option('name'))) {
            $this->authenticate();

            $name = $this->asksForRepository();
        } else {
            $name = $this->option('name');
        }

        Vault::set('repo', $name);

        $this->info('Repository set successfully.');
    }
}
