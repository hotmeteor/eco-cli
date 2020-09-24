<?php

namespace App\Commands;

use App\Support\Vault;
use Illuminate\Support\Arr;

class OrgSwitchCommand extends Command
{
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

        $organizations = $this->driver()->getOrganizations();

        $all_organizations = collect($organizations)->sortBy->login->prepend(
            Arr::only($this->driver()->getCurrentUser(), ['id', 'login'])
        );

        $org_id = $this->menu(
            'Which organization should be used?',
            $all_organizations->mapWithKeys(function ($org) {
                return [$org['id'] => $org['login']];
            })->all()
        )->open();

        Vault::set('org', $all_organizations->firstWhere('id', $org_id)['login']);
        Vault::set('repo', null);

        $this->info('Organization set successfully.');
    }
}
