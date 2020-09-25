<?php

namespace App\Concerns;

use App\Hosts\Data\Organization;
use Illuminate\Support\Collection;

trait AsksForOrganization
{
    protected function asksForOrganization()
    {
        $user = $this->currentUser();

        $organizations = $this->driver()->getOrganizations()->sortBy->login->prepend(
            new Organization($user['id'], $user['login'])
        );

        return $organizations->firstWhere('id', $this->getOrganizationChoice($organizations))->login;
    }

    protected function getOrganizationChoice(Collection $organizations)
    {
        return $this->keyChoice(
            'Which organization should be used?',
            $organizations->mapWithKeys(function ($org) {
                return [$org->id => $org->login];
            })->all()
        );
    }
}
