<?php

namespace App\Hosts\Drivers;

use App\Hosts\Data\File;
use App\Hosts\Data\Organization;
use App\Hosts\Data\Repository;
use Illuminate\Support\Collection;

class FakeDriver extends Driver
{
    protected function client()
    {
    }

    public function authenticate($token)
    {
    }

    public function getCurrentUser()
    {
    }

    public function getOrganizations()
    {
    }

    public function getCurrentUserRepositories($per_page = 100)
    {
    }

    public function getOwnerRepositories($owner, $per_page = 100)
    {
    }

    public function getRepository($owner, $name)
    {
    }

    public function getSecretKey($owner, $repository)
    {
    }

    public function getRemoteFile($owner, $repository, $filename): File
    {
    }

    public function createRemoteFile($owner, $repository, $file, $contents, $message)
    {
    }

    public function updateRemoteFile($owner, $repository, $file, $contents, $message, $sha = null)
    {
    }

    public function mapOrganization($item): Organization
    {
    }

    public function mapRepository($item): Repository
    {
    }
}
