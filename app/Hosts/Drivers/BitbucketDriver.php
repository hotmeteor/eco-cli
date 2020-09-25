<?php

namespace App\Hosts\Drivers;

use App\Hosts\Data\File;
use Bitbucket\Client;

class BitbucketDriver extends Driver
{
    protected function client(): Client
    {
        return $this->client;
    }

    public function authenticate($token)
    {
        if (isset($_ENV['BITBUCKET_API_TOKEN']) || getenv('BITBUCKET_API_TOKEN')) {
            return;
        }

        try {
            $this->client()->authenticate(
                Client::AUTH_OAUTH_TOKEN, $token
            );
        } catch (\Exception $exception) {
            throw new \Exception("Please authenticate using the 'install' command before proceeding.");
        }
    }

    public function getCurrentUser()
    {
        return $this->client()->currentUser()->show();
    }

    public function getOrganizations()
    {
        return $this->client()->currentUser()->listWorkspaces();
    }

    public function getCurrentUserRepositories($per_page = 100)
    {
        return $this->client()->repositories()->list();
    }

    public function getOwnerRepositories($owner, $per_page = 100)
    {
        return $this->client()->workspaces($owner)->projects()->list();
    }

    public function getRepository($owner, $name)
    {
        return $this->client()->workspaces($owner)->projects()->show($name);
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
