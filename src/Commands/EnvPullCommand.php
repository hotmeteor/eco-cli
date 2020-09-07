<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Concerns\DecryptsValues;
use Eco\EcoCli\Helpers;
use Github\HttpClient\Message\ResponseMediator;

class EnvPullCommand extends Command
{
    use DecryptsValues;

    protected $file = '.env';

    protected $example_file = '.env.example';

    protected $eco_file = '.eco';

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

            $org = Helpers::config('org');
            $repo = Helpers::config('repo');

            $this->setupFile($org, $repo);

            $this->assignLocalValues($org, $repo);

            $this->assignRemoteValues($org, $repo);

            Helpers::line("<info>Your {$this->file} file has been synced.</info>");
        }
    }

    protected function setupFile($org, $repo)
    {
        $contents = '';

        try {
            $response = $this->github->api('repo')->contents()->show(
                $org, $repo, $this->example_file,
            );
            $contents = base64_decode($response['content'], true);
        } catch (\Exception $exception) {
        }

        file_put_contents($this->file, $contents, true);
    }

    protected function assignLocalValues($org, $repo)
    {
        $data = Helpers::config("local.{$repo}") ?? [];

        $this->assignValues($data);
    }

    protected function assignRemoteValues($org, $repo)
    {
        $response = $this->github->getHttpClient()->get("/repos/{$org}/{$repo}/actions/secrets/public-key");

        $content = ResponseMediator::getContent($response);

        $public_key = $content['key'];

        $response = $this->github->api('repositories')->contents()->show(
            $org, $repo, $this->eco_file
        );

        $content = json_decode(base64_decode($response['content'], true));

        $values = base64_decode($content->values, true);
        $nonce = base64_decode($content->nonce, true);

        $decrypted = json_decode(self::decrypt($public_key, $values, $nonce), true);

        $this->assignValues($decrypted);
    }

    protected function assignValues(array $data)
    {
        foreach ($data as $key => $value) {
            $this->setLine($this->file, $key, $value);
        }
    }
}
