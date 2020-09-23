<?php

namespace Eco\EcoCli\Hosts\Contracts;

use Eco\EcoCli\Models\File;

interface DriverContract
{
    public function authenticate($token);

    public function getCurrentUser();

    public function getOrganizations();

    public function getCurrentUserRepositories($per_page = 100);

    public function getOwnerRepositories($owner, $per_page = 100);

    public function getRepository($owner, $name);

    public function getSecretKey($owner, $repository);

    public function getRemoteFile($owner, $repository, $filename): File;

    public function createRemoteFile($owner, $repository, $file, $contents, $message);

    public function updateRemoteFile($owner, $repository, $file, $contents, $message, $sha = null);
}
