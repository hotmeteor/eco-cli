<?php

namespace App\Hosts\Drivers;

use App\Hosts\Data\File;
use App\Hosts\Data\Organization;
use App\Hosts\Data\Repository;
use App\Hosts\Data\User;
use Gitlab\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GitlabDriver extends Driver
{
    protected function client(): Client
    {
        return $this->client;
    }

    public function authenticate($tokenOrUsername, $password = null)
    {
        // $this->driver()->setUrl('https://git.yourdomain.com');

        $this->client()->authenticate($tokenOrUsername, Client::AUTH_HTTP_TOKEN);
    }

    public function getCurrentUser(): User
    {
        $user = $this->client()->users()->me();

        return new User($user['id'], $user['username']);
    }

    public function getOrganizations(): Collection
    {
        return $this->collectOrganizations(
            $this->client()->groups()->all()
        );
    }

    public function getCurrentUserRepositories($per_page = 100): Collection
    {
        return $this->collectRepositories(
            $this->client()->projects()->all([
                'membership' => true,
                'owned' => true,
                'simple' => true,
            ])
        );
    }

    public function getOwnerRepositories($owner, $per_page = 100): Collection
    {
        return $this->collectRepositories(
            $this->client()->projects()->all([
                'membership' => true,
                'simple' => true,
            ])
        );
    }

    public function getRepository($owner, $name)
    {
        return $this->client()->projects()->show("$owner/$name");
    }

    public function getSecretKey($owner, $repository)
    {
        $keys = collect($this->client()->projects()->deployKeys("$owner/$repository"));

        if ($keys->isNotEmpty()) {
            $key = $keys->first()['key'];
            $key = trim(Str::between($key, 'ssh-ed25519', 'Gitlab'));
            $key = substr($key, 0, SODIUM_CRYPTO_SECRETBOX_KEYBYTES);

            return base64_encode($key);
        }

        return null;
    }

    public function getRemoteFile($owner, $repository, $filename): File
    {
        $response = $this->client()->repositoryFiles()->getFile(
            "$owner/$repository", $filename, 'master'
        );

        return new File(
            base64_decode($response['content'], true),
            $response['commit_id']
        );
    }

    public function createRemoteFile($owner, $repository, $file, $contents, $message)
    {
        return $this->client()->repositoryFiles()->createFile(
            "$owner/$repository", [
                'branch' => 'master',
                'file_path' => urlencode($file),
                'content' => $contents,
                'commit_message' => $message,
            ]
        );
    }

    public function updateRemoteFile($owner, $repository, $file, $contents, $message, $sha = null)
    {
        return $this->client()->repositoryFiles()->updateFile(
            "$owner/$repository", [
                'branch' => 'master',
                'file_path' => urlencode($file),
                'content' => $contents,
                'commit_message' => $message,
            ]
        );
    }

    public function mapOrganization($item): Organization
    {
        return new Organization($item['id'], $item['name']);
    }

    public function mapRepository($item): Repository
    {
        return new Repository($item['id'], $item['name']);
    }
}
