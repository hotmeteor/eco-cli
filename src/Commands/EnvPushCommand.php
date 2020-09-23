<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;

class EnvPushCommand extends Command
{
    protected $public_key;

    protected $values;

    protected $sha;

    protected function configure()
    {
        $this
            ->setName('env:push')
            ->setAliases(['push'])
            ->addArgument('key', InputArgument::OPTIONAL, 'The key of the value to push')
            ->setDescription('Push up .env values to remote repository.');
    }

    public function handle()
    {
        $this->authenticate();

        $owner = Helpers::config('org');
        $repo = Helpers::config('repo');

        if (!empty($this->argument('key'))) {
            $key = $this->argument('key');
        } else {
            $key = strtoupper(trim(Helpers::ask('Key')));
        }

        $value = Helpers::ask('Value');

        $this->setPublicKey($owner, $repo);

        if ($this->keyExists($owner, $repo, $key)) {
            if (Helpers::confirm('This environment key already exists. Are you sure you want to change it?')) {
                $this->setValue($owner, $repo, $key, $value);
            }
        } else {
            $this->setValue($owner, $repo, $key, $value);
        }
    }

    protected function setPublicKey($owner, $repo): void
    {
        $this->public_key = $this->host->getPublicKey($owner, $repo);
    }

    protected function keyExists($owner, $repo, $key): bool
    {
        try {
            $response = $this->host->getRemoteFile(
                $owner, $repo, $this->eco_file
            );

            $decrypted = $this->host->decryptContents(
                $response['content'], $this->public_key['key']
            );

            $this->sha = $response['sha'];
            $this->values = $decrypted;

            return array_key_exists($key, $decrypted);
        } catch (\Exception $exception) {
            $this->values = [];

            return false;
        }
    }

    protected function setValue($owner, $repo, $key, $value): void
    {
        $payload = $this->host->encryptContents(
            Arr::set($this->values, $key, $value),
            $this->public_key
        );

        try {
            if ($this->sha) {
                $this->host->updateRemoteFile(
                    $owner, $repo, $this->eco_file, $payload, 'Update .eco values', $this->sha
                );
            } else {
                $this->host->createRemoteFile(
                    $owner, $repo, $this->eco_file, $payload, 'Create initial .eco file'
                );
            }

            Helpers::info(Helpers::exclaim().'! The value was successfully added to the .eco file.');
        } catch (\Exception $exception) {
            Helpers::danger('There was an issue updating the .eco file.');
        }
    }
}
