<?php

namespace Eco\EcoCli\Hosts;

interface DriverContract
{
    public function authenticate($token);

    public function getCurrentUser();

    public function getOrganizations();

    public function getRemoteFile($organization, $repository, $filename);

    public function getCurrentUserRepositories($per_page = 100);

    public function getOrganizationRepositories($organization, $per_page = 100);

    public function getRepository($organization, $name);

    public function getSecretKey($organization, $repository);

    public function createRemoteFile($owner, $repo, $file, $contents, $message);

    public function putRemoteFile($owner, $repo, $file, $contents, $message, $sha);
}
