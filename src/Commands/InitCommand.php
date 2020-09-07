<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Config;
use Eco\EcoCli\Helpers;
use GuzzleHttp\Exception\ClientException;

class InitCommand extends Command
{
    /**
     * Configure the command options.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('init')
            ->setDescription('Set up Eco CLI');
    }

    /**
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->attemptLogin();
        } catch (ClientException $e) {
            return $this->displayFailureMessage($e->getResponse());
        }

        $this->ensureCurrentOrgIsSet();
        $this->ensureCurrentRepoIsSet();

        Helpers::info(Helpers::exclaim().'! Eco has been configured.');
    }

    /**
     * Attempt to log in.
     *
     * @return string
     */
    protected function attemptLogin()
    {
        $token = Helpers::secret('Github Token');

        $this->github->authenticate(
            $token, null, \Github\Client::AUTH_ACCESS_TOKEN
        );

        Helpers::config(['token' => $token]);

        Helpers::info('Authenticated successfully.'.PHP_EOL);
    }

    protected function displayFailureMessage($response)
    {
        Helpers::abort(
            'Authentication failed ('.$response->getStatusCode().')'
        );
    }

    protected function ensureCurrentOrgIsSet()
    {
        $organizations = $this->github->currentUser()->organizations();

        $org_id = $this->menu(
            'Which organization should be used?',
            collect($organizations)->sortBy->login->mapWithKeys(function ($org) {
                return [$org['id'] => $org['login']];
            })->all()
        );

        Config::set('org', collect($organizations)->firstWhere('id', $org_id)['login']);

        Helpers::info('Organization set successfully.');
    }

    protected function ensureCurrentRepoIsSet()
    {
        $repos = $this->github->api('organization')->repositories(Helpers::config('org'));

        $repo_id = $this->menu(
            'Which repository should be used? You can always switch this later.',
            collect($repos)->sortBy->name->mapWithKeys(function ($repo) {
                return [$repo['id'] => $repo['name']];
            })->all()
        );

        Config::set('repo', collect($repos)->firstWhere('id', $repo_id)['name']);

        Helpers::info('Repository set successfully.');
    }
}
