<?php

namespace Eco\EcoCli\Hosts\Contracts;

interface DriverContract
{
    public function authenticate($token);

    public function getCurrentUser();

    public function getOrganizations();

    public function getRemoteFile($owner, $repository, $filename);

    public function getCurrentUserRepositories($per_page = 100);

    public function getOwnerRepositories($owner, $per_page = 100);

    public function getRepository($owner, $name);

    public function getSecretKey($owner, $repository);

    public function createRemoteFile($owner, $repository, $file, $contents, $message);

    public function updateRemoteFile($owner, $repository, $file, $contents, $message, $sha = null);

    public function upsertRemoteFile($owner, $repository, $file, $contents, $message, $sha = null);
}
