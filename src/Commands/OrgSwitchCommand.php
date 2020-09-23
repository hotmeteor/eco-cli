<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Config;
use Eco\EcoCli\Helpers;
use Illuminate\Support\Arr;

class OrgSwitchCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('org:switch')
            ->setDescription('Switch to a different organization context');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->authenticate();

        $organizations = $this->host->getOrganizations();

        $all_organizations = collect($organizations)->sortBy->login->prepend(
            Arr::only($this->host->getCurrentUser(), ['id', 'login'])
        );

        $org_id = $this->menu(
            'Which organization should be used?',
            $all_organizations->mapWithKeys(function ($org) {
                return [$org['id'] => $org['login']];
            })->all()
        );

        Config::set('org', $all_organizations->firstWhere('id', $org_id)['login']);
        Config::set('repo', null);

        Helpers::info('Organization set successfully.');
    }
}
