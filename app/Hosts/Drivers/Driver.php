<?php

namespace App\Hosts\Drivers;

use App\Hosts\Concerns\SecuresFileContents;
use App\Hosts\Contracts\DriverContract;
use App\Hosts\Contracts\SecurityContract;
use App\Hosts\Contracts\WithMapping;

abstract class Driver implements DriverContract, SecurityContract, WithMapping
{
    use SecuresFileContents;

    protected $client;

    public function __construct($client = null)
    {
        $this->client = $client;
    }

    abstract protected function client();

    protected function collectOrganizations(array $items)
    {
        return collect($items)->map(function ($item) {
            return static::mapOrganization($item);
        });
    }

    protected function collectRepositories(array $items)
    {
        return collect($items)->map(function ($item) {
            return static::mapRepository($item);
        });
    }
}
