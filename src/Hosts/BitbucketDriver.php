<?php

namespace Eco\EcoCli\Hosts;

use Bitbucket\Client;
use Eco\EcoCli\Models\File;

class BitbucketDriver extends BaseDriver
{
    protected function initialize()
    {
        $this->driver = $this->app->make(Client::class);
    }

    protected function driver(): Client
    {
        return $this->driver;
    }

    public function authenticate($token)
    {
        if (isset($_ENV['BITBUCKET_API_TOKEN']) || getenv('BITBUCKET_API_TOKEN')) {
            return;
        }

        try {
            $this->driver()->authenticate(
                Client::AUTH_OAUTH_TOKEN, $token
            );
        } catch (\Exception $exception) {
            throw new \Exception("Please authenticate using the 'install' command before proceeding.");
        }
    }

    public function getCurrentUser()
    {
        return $this->driver()->currentUser()->show();
    }

    public function getOrganizations()
    {
        return $this->driver()->currentUser()->listWorkspaces();
    }

    public function getCurrentUserRepositories($per_page = 100)
    {
        return $this->driver()->repositories()->list();
    }

    public function getOwnerRepositories($owner, $per_page = 100)
    {
        return $this->driver()->workspaces($owner)->projects()->list();
    }

    public function getRepository($owner, $name)
    {
        return $this->driver()->workspaces($owner)->projects()->show($name);
    }

    public function getSecretKey($owner, $repository)
    {
        // TODO: Implement getSecretKey() method.
    }

    public function getRemoteFile($owner, $repository, $filename): File
    {
        // TODO: Implement getRemoteFile() method.
    }

    public function createRemoteFile($owner, $repository, $file, $contents, $message)
    {
        // TODO: Implement createRemoteFile() method.
    }

    public function updateRemoteFile($owner, $repository, $file, $contents, $message, $sha = null)
    {
        // TODO: Implement updateRemoteFile() method.
    }
}
