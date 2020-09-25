<?php

namespace App\Hosts\Drivers;

use App\Hosts\Data\File;
use App\Hosts\Data\Organization;
use App\Hosts\Data\Repository;
use App\Hosts\Data\User;
use Gitlab\Client;
use Gitlab\HttpClient\Message\ResponseMediator;

class GitlabDriver extends Driver
{
    protected function client(): Client
    {
        return $this->client;
    }

    public function authenticate($token)
    {
        if (isset($_ENV['GITLAB_API_TOKEN']) || getenv('GITLAB_API_TOKEN')) {
            return;
        }

        try {
//            $this->driver()->setUrl('https://git.yourdomain.com');

            $this->client()->authenticate(
                $token, Client::AUTH_HTTP_TOKEN
            );
        } catch (\Exception $exception) {
            throw new \Exception("Please authenticate using the 'install' command before proceeding.");
        }
    }

    public function getCurrentUser(): User
    {
        return $this->client()->users()->me();
    }

    public function getOrganizations()
    {
        return $this->collectOrganizations(
            $this->client()->groups()->all()
        );
    }

    public function getCurrentUserRepositories($per_page = 100)
    {
        return $this->collectRepositories(
            $this->client()->projects()->all([
                'membership' => true,
                'owned' => true,
                'simple' => true,
            ])
        );
    }

    public function getOwnerRepositories($owner, $per_page = 100)
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
        return $this->client()->projects()->show(urlencode($name));
    }

    public function getSecretKey($owner, $repository)
    {
        $id = urlencode($repository);

        $response = $this->client()->getHttpClient()->get("/projects/{$id}/deploy_keys");

        $content = ResponseMediator::getContent($response);

        $keys = collect($content)->whereIn('title', ['eco', 'main']);

        if ($keys->isNotEmpty()) {
            return $keys->first()->key;
        }

        return null;
    }

    public function getRemoteFile($owner, $repository, $filename): File
    {
        $response = $this->client()->repositoryFiles()->getFile(
            $repository, $filename, 'master'
        );

        return new File(
            base64_decode($response['content'], true),
            $response['commit_id']
        );
    }

    public function createRemoteFile($owner, $repository, $file, $contents, $message)
    {
        return $this->client()->repositoryFiles()->createFile(
            urlencode($repository), [
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
            urlencode($repository), [
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
