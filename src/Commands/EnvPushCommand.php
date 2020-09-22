<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Concerns\DecryptsValues;
use Eco\EcoCli\Concerns\EncryptsValues;
use Eco\EcoCli\Helpers;
use Illuminate\Support\Arr;
use Symfony\Component\Console\Input\InputArgument;

class EnvPushCommand extends Command
{
    use EncryptsValues;
    use DecryptsValues;

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

        if ($this->getCurrentContents($owner, $repo, $key)) {
            if (Helpers::confirm('This environment key already exists. Are you sure you want to change it?')) {
                $this->setValue($owner, $repo, $key, $value);
            }
        } else {
            $this->setValue($owner, $repo, $key, $value);
        }
    }

    protected function setPublicKey($owner, $repo)
    {
        $this->public_key = $this->host->getPublicKey($owner, $repo);
    }

    protected function getCurrentContents($owner, $repo, $key)
    {
        try {
            $response = $this->host->getRemoteFile(
                $owner, $repo, $this->eco_file
            );

            $this->sha = $response['sha'];

            $content = json_decode(base64_decode($response['content'], true));

            $values = base64_decode($content->values, true);
            $nonce = base64_decode($content->nonce, true);

            $decrypted = json_decode(self::decrypt($this->public_key['key'], $values, $nonce), true);

            $this->values = $decrypted;

            return array_key_exists($key, $decrypted);
        } catch (\Exception $exception) {
            $this->values = [];

            return false;
        }
    }

    protected function setValue($owner, $repo, $key, $value)
    {
        $values = Arr::set($this->values, $key, $value);

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $payload = json_encode([
            'values' => base64_encode(self::encrypt($this->public_key['key'], json_encode($values), $nonce)),
            'nonce' => base64_encode($nonce),
        ]);

        try {
            if ($this->sha) {
                $this->host->api('repositories')->contents()->update(
                    $owner, $repo, $this->eco_file, $payload, 'Update .eco values', $this->sha
                );
            } else {
                $this->host->api('repositories')->contents()->create(
                    $owner, $repo, $this->eco_file, $payload, 'Create initial .eco file'
                );
            }

            Helpers::info(Helpers::exclaim().'! The value was successfully added to the .eco file.');
        } catch (\Exception $exception) {
            Helpers::danger('There was an issue updating the .eco file.');
        }
    }
}
