<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Config;
use Eco\EcoCli\Helpers;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;

class InitCommand extends Command
{
    protected $current_user;

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

        $this->current_user = $this->github->currentUser()->show();

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
        Helpers::info('----');
        Helpers::info('To start, you will need a Github Personal Access token.');
        Helpers::comment('https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token');

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

        $all_organizations = collect($organizations)->sortBy->login->prepend(
            Arr::only($this->current_user, ['id', 'login'])
        );

        $org_id = $this->menu(
            'Which organization should be used?',
            $all_organizations->mapWithKeys(function ($org) {
                return [$org['id'] => $org['login']];
            })->all()
        );

        Config::set('org', $all_organizations->firstWhere('id', $org_id)['login']);

        Helpers::info('Organization set successfully.');
    }

    protected function ensureCurrentRepoIsSet()
    {
        $repos = Helpers::config('org') === $this->current_user['login']
            ? $this->github->currentUser()->setPerPage(100)->repositories()
            : $this->github->api('organization')->repositories(Helpers::config('org'));

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
