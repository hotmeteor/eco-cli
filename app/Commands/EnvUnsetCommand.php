<?php

namespace App\Commands;

use App\Support\Vault;

class EnvUnsetCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'env:unset
                            {key? : The key of the value to unset (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Unset and delete a local variable';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $key = $this->asksForKey();

        $org = Vault::config('org');
        $repo = Vault::config('repo');

        Vault::unset("{$org}.{$repo}.{$key}");

        $this->unsetLine($this->envFile(), $key);

        $this->output->writeln('<info>The</info> <comment>'.$key.'</comment> <info>value has been deleted and removed from your .env file.</info>');
    }

    protected function asksForKey()
    {
        if (empty($key = $this->argument('key'))) {
            $key = $this->ask('What key should be unset?');
        }

        return strtoupper(trim($key));
    }
}
