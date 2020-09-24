<?php

namespace App\Commands;

use App\Support\Config;
use Illuminate\Support\Arr;

class OrgCurrentCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'org:current';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Determine your current organization context.';

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

        $org = $all_organizations->firstWhere('login', Config::get('org'));

        if (!$org) {
            $this->abort('Unable to determine current organization.');
        }

        $this->output->writeln('<info>You are currently working in the</info> <comment>['.$org['login'].']</comment> <info>organization.</info>');
    }
}
