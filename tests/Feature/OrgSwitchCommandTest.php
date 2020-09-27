<?php

namespace Tests\Feature;

use App\Hosts\Data\Organization;
use App\Hosts\Data\User;
use App\Support\Vault;
use Tests\TestCase;

class OrgSwitchCommandTest extends TestCase
{
    public function test_should_switch_org()
    {
        Vault::config('org', 'my_org');
        Vault::config('repo', 'my_repo');

        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('getOrganizations')
            ->willReturn(
                collect([new Organization(1, 'ecoorg')])
            );

        $mock->expects($this->atLeastOnce())
            ->method('getCurrentUser')
            ->willReturn(new User(2, 'hotmeteor'));

        $this->artisan('org:switch')
            ->expectsQuestion('Which organization should be used?', 2)
            ->expectsOutput('Organization set successfully.')
            ->assertExitCode(0);

        $this->assertSame('hotmeteor', Vault::config('org'));
        $this->assertNull(Vault::config('repo'));
    }
}
