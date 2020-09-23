<?php

namespace Eco\EcoCli\Hosts;

use Eco\EcoCli\Hosts\Concerns\SecuresFileContents;
use Eco\EcoCli\Hosts\Contracts\DriverContract;
use Eco\EcoCli\Hosts\Contracts\SecurityContract;

abstract class BaseDriver implements DriverContract, SecurityContract
{
    use SecuresFileContents;

    protected $client;

    public function __construct($client)
    {
        $this->client = $client;
    }

    abstract protected function client();
}
