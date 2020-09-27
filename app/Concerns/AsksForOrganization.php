<?php

namespace App\Concerns;

use App\Support\Vault;
use Illuminate\Support\Collection;

trait AsksForOrganization
{
    protected function asksForOrganization()
    {
        $organizations = $this->driver()->getOrganizations()->sortBy->login;

        if (Vault::get('driver') !== 'bitbucket') {
            $organizations->prepend($this->currentUser());
        }

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
