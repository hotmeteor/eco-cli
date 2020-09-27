<?php

namespace App\Commands;

use App\Support\Vault;

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
        if (!$org = Vault::config('org')) {
            $this->abort('Unable to determine current organization.');
        }

        $this->output->writeln('<info>You are currently working in the</info> <comment>['.$org.']</comment> <info>organization.</info>');
    }
}
