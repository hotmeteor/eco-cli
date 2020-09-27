<?php

namespace App\Commands;

use App\Support\Vault;

class EnvFreshCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'env:fresh';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Get a fresh .env file based on remote .env.example.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        if ($this->confirm('Are you sure you want a fresh .env? This will overwrite your existing .env file.')) {
            $this->authenticate();

            $file = $this->driver()->getRemoteFile(
                Vault::config('org'), Vault::config('repo'), $this->env_example_file
            );

            if (!$file) {
                $this->abort("Unable to find {$this->env_example_file} file in repo.");
            }

            file_put_contents($this->envFile(), $file->contents);

            $this->output->writeln('<info>Your</info> <comment>.env</comment> <info>file has been refreshed.</info>');
        }
    }
}
