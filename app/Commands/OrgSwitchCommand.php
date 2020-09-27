<?php

namespace App\Commands;

use App\Concerns\AsksForOrganization;
use App\Support\Vault;

class OrgSwitchCommand extends Command
{
    use AsksForOrganization;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'org:switch';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Switch to a different organization context.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->authenticate();

        Vault::config('org', $this->asksForOrganization());
        Vault::config('repo', '');

        $this->info('Organization set successfully.');
    }
}
