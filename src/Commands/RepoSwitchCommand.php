<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Config;
use Eco\EcoCli\Helpers;
use Symfony\Component\Console\Input\InputOption;

class RepoSwitchCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('repo:switch')
            ->setAliases(['switch'])
            ->addOption('id', null, InputOption::VALUE_OPTIONAL, 'The ID of the repo to switch to')
            ->setDescription('Switch to a different repo context, you may optionally pass Repo Name');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->authenticate();

        $repos = $this->host->currentUser()->repositories(Helpers::config('org'));

        $repo_id = null;

        if (!empty($this->option('id'))) {
            $repo = collect($repos)->where('name', $this->option('name'))->first();

            if (empty($repo)) {
                Helpers::abort('Repo not found.');
            }

            $repo_id = $repo['id'];
        }

        if (is_null($repo_id)) {
            $repo_id = $this->menu(
                'Which repo would you like to switch to?',
                collect($repos)->sortBy->name->mapWithKeys(function ($repo) {
                    return [$repo['id'] => $repo['name']];
                })->all()
            );
        }

        Config::set('repo', collect($repos)->firstWhere('id', $repo_id)['name']);

        Helpers::info('Current repo changed successfully.');
    }
}
