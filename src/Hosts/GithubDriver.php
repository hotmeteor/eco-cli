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
        if (isset($_ENV['GITHUB_API_TOKEN']) || getenv('GITHUB_API_TOKEN')) {
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

    public function getOwnerRepositories($owner, $per_page = 100)
    {
        return $this->driver->currentUser()->repositories($owner)->setPerPage($per_page);
//        return $this->driver->api('organization')->repositories($owner);
    }

    public function getCurrentUserRepositories($per_page = 100)
    {
        return $this->driver->currentUser()->setPerPage($per_page)->repositories();
    }

    public function getRepository($owner, $name)
    {
        return $this->driver->repository()->show($owner, $name);
    }

    public function getSecretKey($owner, $repository)
    {
        $response = $this->driver->getHttpClient()->get("/repos/{$owner}/{$repository}/actions/secrets/public-key");

        $content = ResponseMediator::getContent($response);

        return $content['key'];
    }

    public function getRemoteFile($owner, $repository, $filename)
    {
        return $this->driver->api('repositories')->contents()->show(
            $owner, $repository, $filename
        );
    }

    public function createRemoteFile($owner, $repository, $file, $contents, $message)
    {
        return $this->driver->api('repositories')->contents()->create(
            $owner, $repository, $file, $contents, $message
        );
    }

    public function updateRemoteFile($owner, $repository, $file, $contents, $message, $sha = null)
    {
        return $this->driver->api('repositories')->contents()->update(
            $owner, $repository, $file, $contents, $message, $sha
        );
    }

    public function upsertRemoteFile($owner, $repository, $file, $contents, $message, $sha = null)
    {
        if ($this->driver->exists($owner, $repository, $file)) {
            return $this->updateRemoteFile(
                $owner, $repository, $file, $contents, $message, $sha
            );
        }

        return $this->createRemoteFile(
            $owner, $repository, $file, $contents, $message
        );
    }
}
