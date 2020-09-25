<?php

namespace Tests\Feature;

use App\Support\Vault;
use Tests\TestCase;

class OrgCurrentCommandTest extends TestCase
{
    public function test_should_show_current_org()
    {
        Vault::set('org', 'hotmeteor');

        $this->artisan('org:current')
            ->expectsOutput('You are currently working in the [hotmeteor] organization.')
            ->assertExitCode(0);
    }
}
