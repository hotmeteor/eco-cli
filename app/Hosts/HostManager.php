<?php

namespace App\Hosts;

use App\Hosts\Contracts\DriverContract;
use App\Hosts\Drivers\BitbucketDriver;
use App\Hosts\Drivers\GithubDriver;
use App\Hosts\Drivers\GitlabDriver;
use App\Support\Vault;
use Bitbucket\Client as BitbucketClient;
use Github\Client as GithubClient;
use Gitlab\Client as GitlabClient;
use Illuminate\Support\Manager;

class HostManager extends Manager
{
    public function getDefaultDriver()
    {
        return Vault::get('driver') ?? 'github';
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
