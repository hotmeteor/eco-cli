<?php

namespace App\Concerns;

use App\Support\Vault;
use Illuminate\Support\Collection;

trait AsksForRepository
{
    protected function asksForRepository()
    {
        $org = Vault::config('org');

        $repos = $org === $this->currentUser()->login
            ? $this->driver()->getCurrentUserRepositories()
            : $this->driver()->getOwnerRepositories($org);

        $id = $this->getRepositoryChoice($repos);

        return is_numeric($id) ? $repos->firstWhere('id', $id)->name : $id;
    }

    protected function getRepositoryChoice(Collection $repositories)
    {
        return $this->choice(
            'Which repository should be used? You can always switch this later.',
            $repositories->sortBy->name->mapWithKeys(function ($repo) {
                return [$repo->id => $repo->name];
            })->all()
        );
    }
}
