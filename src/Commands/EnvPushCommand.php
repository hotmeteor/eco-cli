<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;
use Github\HttpClient\Message\ResponseMediator;
use Symfony\Component\Console\Input\InputOption;

class EnvPushCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('env:push')
            ->setAliases(['push'])
            ->addOption('key', null, InputOption::VALUE_OPTIONAL, 'The key of the value to push')
            ->addOption('hidden', 'H', InputOption::VALUE_OPTIONAL, 'If the value should be encrypted')
            ->setDescription('Push up .env values to remote repository.');
    }

    public function handle()
    {
        dd('This is not working.');

        $this->authenticate();

        if (!empty($this->option('key'))) {
            $key = $this->option('key');
        } else {
            $key = strtoupper(trim(Helpers::ask('Key')));
        }

        $value = Helpers::ask('Value');

        $owner = Helpers::config('org');
        $repo = Helpers::config('repo');

        $response = $this->github->getHttpClient()->get("/repos/{$owner}/{$repo}/actions/secrets/TEST");

        dd(ResponseMediator::getContent($response));

        $secrets = $this->github->getHttpClient()->get("/repos/{$owner}/{$repo}/actions/secrets");

        if (collect($secrets)->where('name', $key)->isEmpty()) {
            $this->setValue($owner, $repo, $key, $value);
        } else {
            if (Helpers::confirm('This environment key already exists. Are you sure you want to change it?')) {
                $this->setValue($owner, $repo, $key, $value);
            }
        }
    }

    protected function setValue($owner, $repo, $key, $value)
    {
        $key = 'TEST';

        $response = $this->github->getHttpClient()->get("/repos/{$owner}/{$repo}/actions/secrets/public-key");

        $content = ResponseMediator::getContent($response);

        $payload = json_encode([
            'encrypted_value' => $this->encrypt($value, $content['key']),
            'key_id' => $content['key_id'],
        ]);

        $this->github->getHttpClient()->put("/repos/{$owner}/{$repo}/actions/secrets/{$key}", [], $payload);
    }

    protected function encrypt($value, $key)
    {
        $decoded = base64_decode($key, true);

        return base64_encode(sodium_crypto_auth($value, $decoded));
    }
}
