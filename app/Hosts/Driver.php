<?php

namespace App\Hosts;

use App\Hosts\Concerns\SecuresFileContents;
use App\Hosts\Contracts\DriverContract;
use App\Hosts\Contracts\SecurityContract;

abstract class Driver implements DriverContract, SecurityContract
{
    use SecuresFileContents;

    protected $client;

    public function __construct($client = null)
    {
        $this->client = $client;
    }

    abstract protected function client();
}
