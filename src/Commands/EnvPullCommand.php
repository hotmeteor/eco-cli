<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;
use Eco\EcoCli\Hosts\Concerns\DecryptsValues;

class EnvPullCommand extends Command
{
    use DecryptsValues;

    protected function configure()
    {
        $this
            ->setName('env:pull')
            ->setAliases(['pull'])
            ->setDescription('Pull down the remote .env and sync with your local settings.');
    }

    public function handle()
    {
        Helpers::info('Syncing will use your local variables, but ask you about conflicting remote variables.');

        $this->authenticate();

        $org = Helpers::config('org');
        $repo = Helpers::config('repo');

        $this->setupFile();

        $this->assignLocalValues($org, $repo);

        $this->assignRemoteValues($org, $repo);

        Helpers::line("<info>Your {$this->env_file} file has been synced.</info>");
    }

    protected function setupFile()
    {
        if (!file_exists($this->env_file)) {
            file_put_contents($this->env_file, '', true);
        }
    }

    protected function assignLocalValues($org, $repo)
    {
        $data = Helpers::config("local.{$repo}") ?? [];

        foreach ($data as $key => $value) {
            $this->setLine($this->env_file, $key, $value);
        }

        Helpers::comment('Local variables synced...');
    }

    protected function assignRemoteValues($owner, $repo)
    {
        $file = $this->host->getRemoteFile(
            $owner, $repo, $this->eco_file
        );

        $data = $this->host->decryptContents(
            $file->contents, $this->host->getPublicKey()
        );

        $synced = false;

        foreach ($data as $key => $value) {
            if ($this->findLine($this->env_file, $key)) {
                if (Helpers::confirm("The {$key} variable already exists in your local .env. Do you want to overwrite it?")) {
                    $this->setLine($this->env_file, $key, $value);
                    $synced = true;
                }
            } else {
                $this->setLine($this->env_file, $key, $value);
                $synced = true;
            }
        }

        if ($synced) {
            Helpers::comment('Remote variables synced...');
        }
    }
}
