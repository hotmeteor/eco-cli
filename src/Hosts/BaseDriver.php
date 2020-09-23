<?php

namespace Eco\EcoCli\Hosts;

use Eco\EcoCli\Hosts\Concerns\SecuresFileContents;
use Eco\EcoCli\Hosts\Contracts\DriverContract;
use Eco\EcoCli\Hosts\Contracts\SecurityContract;
use Illuminate\Container\Container;

abstract class BaseDriver implements DriverContract, SecurityContract
{
    use SecuresFileContents;

    protected $app;

    protected $driver;

    public function __construct(Container $app)
    {
        $this->app = $app;

        $this->initialize();
    }

    abstract protected function initialize();
}
