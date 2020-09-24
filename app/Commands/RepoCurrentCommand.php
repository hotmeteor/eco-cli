<?php

namespace App\Commands;

use App\Support\Config;

class RepoCurrentCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'repo:current';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Determine your current repo context.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->authenticate();

        if (!$repo = $this->driver()->getRepository(Config::get('org'), Config::get('repo'))) {
            $this->abort('Unable to determine current repo.');
        }

        $this->output->writeln('<info>You are currently working in the</info> <comment>['.$repo['name'].']</comment> <info>repository.</info>');
    }
}
