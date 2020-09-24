<?php

namespace App\Commands;

use App\Support\Config;
use App\Support\Helpers;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Arr;

class InitCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'init';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Set up Eco CLI';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->attemptLogin();
        } catch (ClientException $e) {
            return $this->displayFailureMessage($e->getResponse());
        }

        $this->current_user = $this->driver()->getCurrentUser();

        $this->ensureCurrentOrgIsSet();
        $this->ensureCurrentRepoIsSet();

        $this->info(Helpers::exclaim().'! Eco has been configured.');
    }

    /**
     * Attempt to log in.
     *
     * @return string
     */
    protected function attemptLogin()
    {
        $this->info('----');
        $this->info('To start, you will need a Github Personal Access token.');
        $this->comment('https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token');

        Config::set('token', '');

        $this->authenticate();

        $this->info('Authenticated successfully.'.PHP_EOL);
    }

    protected function displayFailureMessage($response)
    {
        $this->abort('Authentication failed ('.$response->getStatusCode().')');
    }

    protected function ensureCurrentOrgIsSet()
    {
        $organizations = $this->driver()->getOrganizations();

        $all_organizations = collect($organizations)->sortBy->login->prepend(
            Arr::only($this->current_user, ['id', 'login'])
        );

        $org_id = $this->menu(
            'Which organization should be used?',
            $all_organizations->mapWithKeys(function ($org) {
                return [$org['id'] => $org['login']];
            })->all()
        )->open();

        Config::set('org', $all_organizations->firstWhere('id', $org_id)['login']);

        $this->info('Organization set successfully.');
    }

    protected function ensureCurrentRepoIsSet()
    {
        $repos = Config::get('org') === $this->current_user['login']
            ? $this->driver()->getCurrentUserRepositories()
            : $this->driver()->getOwnerRepositories(Config::get('org'));

        $repo_id = $this->choice(
            'Which repository should be used? You can always switch this later.',
            collect($repos)->sortBy->name->mapWithKeys(function ($repo) {
                return [$repo['id'] => $repo['name']];
            })->all()
        );

        $key = is_numeric($repo_id) ? 'id' : 'name';

        Config::set('repo', collect($repos)->firstWhere($key, $repo_id)['name']);

        $this->info('Repository set successfully.');
    }
}
