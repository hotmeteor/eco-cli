<?php

namespace Eco\EcoCli\Hosts;

use Illuminate\Support\Manager;

class HostManager extends Manager
{
    public function getDefaultDriver()
    {
        return 'github';
    }
}
