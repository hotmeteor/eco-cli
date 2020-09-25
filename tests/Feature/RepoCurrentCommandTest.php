<?php

namespace Tests\Feature;

use App\Support\Vault;
use Tests\TestCase;

class RepoCurrentCommandTest extends TestCase
{
    public function test_should_show_current_org()
    {
        Vault::set('repo', 'eco-cli');

        $this->artisan('repo:current')
            ->expectsOutput('You are currently working in the [eco-cli] repository.')
            ->assertExitCode(0);
    }
}
