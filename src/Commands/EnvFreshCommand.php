<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;

class EnvFreshCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('env:fresh')
            ->setAliases(['cascade'])
            ->setDescription('Get a fresh .env file based on .env.example');
    }

    public function handle()
    {
        if (Helpers::confirm('Are you sure you want a fresh .env? This will overwrite your existing .env file.', false)) {
            $this->authenticate();

            $response = $this->github->api('repo')->contents()->show(
                Helpers::config('org'), Helpers::config('repo'), '.env.example'
            );

            if (!$response) {
                Helpers::abort('Unable to find .env.example file in repo.');
            }

            $file = '.env';

            file_put_contents($file, base64_decode($response['content'], true));

            Helpers::line('<info>Your</info> <comment>.env</comment> <info>file has been refreshed.</info>');
        }
    }
}
