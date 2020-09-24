<?php

namespace App\Commands;

use App\Support\Vault;

class EnvSetCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'env:set
                            {key? : The key of the value to set (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Set and store a local variable';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $key = $this->asksForKey();

        $value = $this->ask('What is the value?');

        $org = Vault::get('org');
        $repo = Vault::get('repo');

        Vault::set("{$org}.{$repo}.{$key}", $value);

        $this->setLine($this->envFile(), $key, $value);

        $this->output->writeln('<info>The</info> <comment>'.$key.'</comment> <info>value has been stored and added to your .env file.</info>');
    }

    protected function asksForKey()
    {
        if (empty($key = $this->argument('key'))) {
            $key = $this->ask('What key should be set?');
        }

        return $key;
    }
}
