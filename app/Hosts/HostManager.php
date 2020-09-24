<?php

namespace App\Hosts;

use App\Hosts\Contracts\DriverContract;
use Bitbucket\Client as BitbucketClient;
use Github\Client as GithubClient;
use Gitlab\Client as GitlabClient;
use Illuminate\Support\Manager;

class HostManager extends Manager
{
    public function getDefaultDriver()
    {
        return 'github';
    }

    public function createGithubDriver(): DriverContract
    {
        return new GithubDriver(new GithubClient());
    }

    public function createGitlabDriver(): DriverContract
    {
        return new GitlabDriver(new GitlabClient());
    }

    public function createBitbucketDriver(): DriverContract
    {
        return new BitbucketDriver(new BitbucketClient());
    }
}
