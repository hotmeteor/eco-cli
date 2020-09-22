<?php

namespace Eco\EcoCli\Hosts;

use Github\Client;
use Github\HttpClient\Message\ResponseMediator;

class GithubDriver extends BaseDriver
{
    protected function initialize()
    {
        $this->driver = $this->app->make(Client::class);
    }

    public function authenticate($token)
    {
        if (isset($_ENV['GITHUB_API_TOKEN']) ||
            getenv('GITHUB_API_TOKEN')) {
            return;
        }

        try {
            $this->driver->authenticate(
                $token, null, Client::AUTH_ACCESS_TOKEN
            );
        } catch (\Exception $exception) {
            throw new \Exception("Please authenticate using the 'install' command before proceeding.");
        }
    }

    public function getCurrentUser()
    {
        return $this->driver->currentUser()->show();
    }

    public function getOrganizations()
    {
        return $this->driver->currentUser()->organizations();
    }

    public function getOrganizationRepositories($organization, $per_page = 100)
    {
        return $this->driver->api('organization')->setPerPage($per_page)->repositories($organization);
    }

    public function getCurrentUserRepositories($per_page = 100)
    {
        return $this->driver->currentUser()->setPerPage($per_page)->repositories();
    }

    public function getRepository($organization, $name)
    {
        // TODO: Implement getRepository() method.
    }

    public function getSecretKey($organization, $repository)
    {
        $response = $this->driver->getHttpClient()->get("/repos/{$organization}/{$repository}/actions/secrets/public-key");

        $content = ResponseMediator::getContent($response);

        return $content['key'];
    }

    public function getRemoteFile($organization, $repository, $filename)
    {
        return $this->driver->api('repositories')->contents()->show(
            $organization, $repository, $filename
        );
    }

    public function createRemoteFile($owner, $repo, $file, $contents, $message)
    {
        // TODO: Implement createRemoteFile() method.
    }

    public function putRemoteFile($owner, $repo, $file, $contents, $message, $sha)
    {
        // TODO: Implement putRemoteFile() method.
    }
}
