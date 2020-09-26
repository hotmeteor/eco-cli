<?php

namespace App\Hosts\Drivers;

use App\Hosts\Data\File;
use App\Hosts\Data\Organization;
use App\Hosts\Data\Repository;
use App\Hosts\Data\User;
use App\Support\Vault;
use Bitbucket\Client;
use Bitbucket\HttpClient\Message\FileResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class BitbucketDriver extends Driver
{
    protected function client(): Client
    {
        return $this->client;
    }

    public function authenticate($tokenOrUsername, $password = null)
    {
        $this->client()->authenticate(Client::AUTH_HTTP_PASSWORD, $tokenOrUsername, $password);
    }

    public function getCurrentUser()
    {
        $user = $this->client()->currentUser()->show();

        return new User($user['uuid'], $user['username']);
    }

    public function getOrganizations()
    {
        return $this->collectOrganizations(
            Arr::get($this->client()->currentUser()->listWorkspaces(), 'values', [])
        );
    }

    public function getCurrentUserRepositories($per_page = 100)
    {
        return $this->getOwnerRepositories(Vault::get('username'));
    }

    public function getOwnerRepositories($owner, $per_page = 100)
    {
        return $this->collectRepositories(
            Arr::get($this->client()->repositories()->workspaces($owner)->list(), 'values', [])
        );
    }

    public function getRepository($owner, $name)
    {
        return $this->client()->repositories()->workspaces($owner)->show($name);
    }

    public function getSecretKey($owner, $repository)
    {
        $keys = collect($this->client()->repositories()->workspaces($owner)->deployKeys($repository)->list()['values']);

        if ($keys->contains('label', 'eco-cli')) {
            $key = $keys->firstWhere('label', 'eco-cli')['key'];
            $key = trim(Str::after($key, 'ssh-rsa'));
            $key = substr($key, 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

            return base64_encode($key);
        }
    }

    public function getRemoteFile($owner, $repository, $filename): File
    {
        $file = $this->client()->repositories()->workspaces($owner)->src($repository)->download('master', $filename);

        $contents = $file->getContents();

        return new File($contents, base64_encode($contents));
    }

    public function createRemoteFile($owner, $repository, $file, $contents, $message)
    {
        return $this->updateRemoteFile($owner, $repository, $file, $contents, $message);
    }

    public function updateRemoteFile($owner, $repository, $file, $contents, $message, $sha = null)
    {
        return $this->client()->repositories()->workspaces($owner)->src($repository)->createWithFiles(
            [new FileResource($file, $contents)],
            ['message' => $message]
        );
    }

    public function mapOrganization($item): Organization
    {
        return new Organization($item['slug'], $item['name']);
    }

    public function mapRepository($item): Repository
    {
        return new Repository($item['uuid'], $item['name']);
    }
}
