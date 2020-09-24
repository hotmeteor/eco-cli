<?php

namespace App\Commands;

use App\Support\Config;

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
        if ($this->confirm('Are you sure you want a fresh .env? This will overwrite your existing .env file.', false)) {
            $this->authenticate();

            $response = $this->driver()->getRemoteFile(
                Config::get('org'), Config::get('repo'), '.env.example'
            );

            if (!$response) {
                $this->abort('Unable to find .env.example file in repo.');
            }

            file_put_contents($this->env_file, base64_decode($response['content'], true));

            $this->output->writeln('<info>Your</info> <comment>.env</comment> <info>file has been refreshed.</info>');
        }
    }
}
