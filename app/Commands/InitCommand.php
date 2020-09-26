<?php

namespace App\Commands;

use App\Concerns\AsksForOrganization;
use App\Concerns\AsksForRepository;
use App\Support\Helpers;
use App\Support\Vault;
use GuzzleHttp\Exception\ClientException;

class InitCommand extends Command
{
    use AsksForOrganization;
    use AsksForRepository;

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
//        $this->info('To start, you will need a Github Personal Access token.');
//        $this->comment('https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token');

        Vault::unset('driver');

        $this->authenticate();

        $this->info('Authenticated successfully.'.PHP_EOL);
    }

    protected function displayFailureMessage($response)
    {
        $this->abort('Authentication failed ('.$response->getStatusCode().')');
    }

    protected function ensureCurrentOrgIsSet()
    {
        Vault::config('org', $this->asksForOrganization());

        $this->info('Organization set successfully.');
    }

    protected function ensureCurrentRepoIsSet()
    {
        Vault::config('repo', $this->asksForRepository());

        $this->info('Repository set successfully.');
    }
}
