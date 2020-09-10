<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;
use Illuminate\Support\Arr;

class OrgCurrentCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('org:current')
            ->setDescription('Determine your current organization context');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->authenticate();

        $organizations = $this->github->currentUser()->organizations();

        $all_organizations = collect($organizations)->sortBy->login->prepend(
            Arr::only($this->github->currentUser()->show(), ['id', 'login'])
        );

        $org = $all_organizations->firstWhere('login', Helpers::config('org'));

        if (!$org) {
            Helpers::abort('Unable to determine current organization.');
        }

        Helpers::line('<info>You are currently working in the</info> <comment>['.$org['login'].']</comment> <info>organization.</info>');
    }
}
