<?php

namespace Eco\EcoCli\Hosts;

use Illuminate\Container\Container;

abstract class BaseDriver implements DriverContract
{
    protected $app;

    protected $driver;

    public function __construct(Container $app)
    {
        $this->app = $app;

        $this->initialize();
    }

    abstract protected function initialize();
}
