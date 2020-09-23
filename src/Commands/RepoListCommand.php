<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;

class RepoListCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('repo:list')
            ->setAliases(['repos'])
            ->setDescription('List the repos that you belong to');
    }

    public function handle()
    {
        $this->authenticate();

        $repos = $this->host->getOwnerRepositories(Helpers::config('org'));

        $repos = collect($repos)->sortBy(function ($repo) {
            return $repo['name'];
        })->all();

        $this->table([
            'Name', 'Private',
        ], collect($repos)->map(function ($repo) {
            return [
                $repo['name'],
                $repo['private'] ? 'X' : '',
            ];
        })->all());
    }
}
