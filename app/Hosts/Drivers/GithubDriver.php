<?php

namespace App\Hosts\Drivers;

use App\Hosts\Data\File;
use App\Hosts\Data\Organization;
use App\Hosts\Data\Repository;
use App\Hosts\Data\User;
use Github\Client;
use Github\HttpClient\Message\ResponseMediator;
use Illuminate\Support\Collection;

class GithubDriver extends Driver
{
    protected function client(): Client
    {
        return $this->client;
    }

    public function authenticate($tokenOrUsername, $password = null)
    {
        $this->client()->authenticate($tokenOrUsername, null, Client::AUTH_ACCESS_TOKEN);
    }

    public function getCurrentUser(): User
    {
        $user = $this->client()->currentUser()->show();

        return new User($user['id'], $user['login']);
    }

    public function getOrganizations(): Collection
    {
        return $this->collectOrganizations(
            $this->client()->currentUser()->organizations()
        );
    }

    public function getOwnerRepositories($owner, $per_page = 100): Collection
    {
        return $this->collectRepositories(
            $this->client()->api('organization')->setPerPage($per_page)->repositories($owner)
        );
    }

    public function getCurrentUserRepositories($per_page = 100): Collection
    {
        return $this->collectRepositories(
            $this->client()->currentUser()->setPerPage($per_page)->repositories()
        );
    }

    public function getRepository($owner, $name)
    {
        return $this->client()->repository()->show($owner, $name);
    }

    public function getSecretKey($owner, $repository)
    {
        $response = $this->client()->getHttpClient()->get("/repos/{$owner}/{$repository}/actions/secrets/public-key");

        $content = ResponseMediator::getContent($response);

        return $content['key'];
    }

    public function getRemoteFile($owner, $repository, $filename): File
    {
        $response = $this->client()->api('repositories')->contents()->show(
            $owner, $repository, $filename
        );

        return new File(
            base64_decode($response['content'], true),
            $response['sha']
        );
    }

    public function createRemoteFile($owner, $repository, $file, $contents, $message)
    {
        return $this->client()->api('repositories')->contents()->create(
            $owner, $repository, $file, $contents, $message
        );
    }

    public function updateRemoteFile($owner, $repository, $file, $contents, $message, $sha = null)
    {
        return $this->client()->api('repositories')->contents()->update(
            $owner, $repository, $file, $contents, $message, $sha
        );
    }

    public function mapOrganization($item): Organization
    {
        return new Organization($item['id'], $item['login']);
    }

    public function mapRepository($item): Repository
    {
        return new Repository($item['id'], $item['name']);
    }
}
