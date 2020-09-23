<?php

namespace Eco\EcoCli\Hosts;

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

    public function createGithubDriver()
    {
        return new GithubDriver(new GithubClient());
    }

    public function createGitlabDriver()
    {
        return new GitlabDriver(new GitlabClient());
    }

    public function createBitbucketDriver()
    {
        return new BitbucketDriver(new BitbucketClient());
    }
}
