<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;

class EnvPullCommand extends Command
{
    protected $file = '.env';

    protected function configure()
    {
        $this
            ->setName('env:pull')
            ->setAliases(['pull'])
            ->setDescription('Pull down the remote .env and sync with your local settings.');
    }

    public function handle()
    {
        if (Helpers::confirm('Are you sure you want a sync your .env?', false)) {
            $this->authenticate();

            $this->setupFile();

            $this->setLocalValues();

            Helpers::line("<info>Your {$this->file} file has been synced.</info>");
        }
    }

    protected function setupFile()
    {
        $response = $this->github->api('repo')->contents()->show(
            Helpers::config('org'), Helpers::config('repo'), '.env.example'
        );

        if (!$response) {
            Helpers::abort('Unable to find .env.example file in repo.');
        }

        file_put_contents($this->file, base64_decode($response['content'], true));
    }

    protected function setLocalValues()
    {
        $repo = Helpers::config('repo');

        foreach (Helpers::config("local.{$repo}") as $key => $value) {
            $this->setLine($this->file, $key, $value);
        }
    }
}
