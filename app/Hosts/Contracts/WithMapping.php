<?php

namespace App\Hosts\Contracts;

use App\Hosts\Data\Organization;
use App\Hosts\Data\Repository;

interface WithMapping
{
    public function mapOrganization($item): Organization;

    public function mapRepository($item): Repository;
}
