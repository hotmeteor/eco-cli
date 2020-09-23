<?php

namespace Eco\EcoCli\Hosts;

use Eco\EcoCli\Models\File;
use Gitlab\Client;
use Gitlab\HttpClient\Message\ResponseMediator;

class GitlabDriver extends BaseDriver
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
        if (isset($_ENV['GITLAB_API_TOKEN']) || getenv('GITLAB_API_TOKEN')) {
            return;
        }

        try {
//            $this->driver()->setUrl('https://git.yourdomain.com');

            $this->driver()->authenticate(
                $token, Client::AUTH_HTTP_TOKEN
            );
        } catch (\Exception $exception) {
            throw new \Exception("Please authenticate using the 'install' command before proceeding.");
        }
    }

    public function getCurrentUser()
    {
        return $this->driver()->users()->me();
    }

    public function getOrganizations()
    {
        return $this->driver()->groups();
    }

    public function getCurrentUserRepositories($per_page = 100)
    {
        return $this->driver()->projects()->all([
            'membership' => true,
            'owned' => true,
            'simple' => true,
        ]);
    }

    public function getOwnerRepositories($owner, $per_page = 100)
    {
        return $this->driver()->projects()->all([
            'membership' => true,
            'simple' => true,
        ]);
    }

    public function getRepository($owner, $name)
    {
        return $this->driver()->projects()->show(urlencode($name));
    }

    public function getSecretKey($owner, $repository)
    {
        $id = urlencode($repository);

        $response = $this->driver()->getHttpClient()->get("/projects/{$id}/deploy_keys");

        $content = ResponseMediator::getContent($response);

        $keys = collect($content)->whereIn('title', ['eco', 'main']);

        if ($keys->isNotEmpty()) {
            return $keys->first()->key;
        }

        return null;
    }

    public function getRemoteFile($owner, $repository, $filename): File
    {
        $response = $this->driver()->repositoryFiles()->getFile(
            $repository, $filename, 'master'
        );

        return new File(
            base64_decode($response['content'], true),
            $response['commit_id']
        );
    }

    public function createRemoteFile($owner, $repository, $file, $contents, $message)
    {
        return $this->driver()->repositoryFiles()->createFile(
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
        return $this->driver()->repositoryFiles()->updateFile(
            urlencode($repository), [
                'branch' => 'master',
                'file_path' => urlencode($file),
                'content' => $contents,
                'commit_message' => $message,
            ]
        );
    }
}
