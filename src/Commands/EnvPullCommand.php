<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Hosts\Concerns\DecryptsValues;
use Eco\EcoCli\Helpers;

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

    protected function assignRemoteValues($org, $repo)
    {
        try {
            $eco_file = $this->host->getRemoteFile(
                $org, $repo, $this->eco_file
            );
        } catch (\Exception $exception) {
            return;
        }

        $public_key = $this->host->getPublicKey();

        $content = json_decode(base64_decode($eco_file['content'], true));

        $values = base64_decode($content->values, true);
        $nonce = base64_decode($content->nonce, true);

        $data = json_decode(self::decrypt($public_key, $values, $nonce), true);

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
