<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;

class RepoCurrentCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('repo:current')
            ->setAliases(['current'])
            ->setDescription('Determine your current repo context');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->authenticate();

        $repo = $this->host->repository()->show(
            Helpers::config('org'), Helpers::config('repo')
        );

        if (!$repo) {
            Helpers::abort('Unable to determine current repo.');
        }

        Helpers::line('<info>You are currently working in the</info> <comment>['.$repo['name'].']</comment> <info>repository.</info>');
    }
}
