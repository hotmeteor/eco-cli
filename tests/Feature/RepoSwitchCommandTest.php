<?php

namespace Tests\Feature;

use App\Hosts\Data\Repository;
use App\Support\Vault;
use Tests\TestCase;

class RepoSwitchCommandTest extends TestCase
{
    public function test_should_switch_repo_using_id()
    {
        Vault::set('org', 'hotmeteor');
        Vault::set('repo', 'this_repo');

        $mock = $this->mockDriver();
        $this->mockAuthenticate($mock);

        $mock->expects($this->once())
            ->method('getCurrentUserRepositories')
            ->willReturn(
                collect([
                    new Repository(3333, 'eco-cli'),
                    new Repository(4444, 'other_repo'),
                ])
            );

        $this->artisan('repo:switch')
            ->expectsQuestion('Which repository should be used? You can always switch this later.', 4444)
            ->expectsOutput('Repository set successfully.')
            ->assertExitCode(0);

        $this->assertSame('hotmeteor', Vault::get('org'));
        $this->assertSame('other_repo', Vault::get('repo'));
    }

    public function test_should_switch_repo_using_name()
    {
        Vault::set('org', 'hotmeteor');
        Vault::set('repo', 'this_repo');

        $mock = $this->mockDriver();
        $this->mockAuthenticate($mock);

        $mock->expects($this->once())
            ->method('getCurrentUserRepositories')
            ->willReturn(
                collect([
                    new Repository(3333, 'eco-cli'),
                    new Repository(4444, 'other_repo'),
                ])
            );

        $this->artisan('repo:switch')
            ->expectsQuestion('Which repository should be used? You can always switch this later.', 'eco-cli')
            ->expectsOutput('Repository set successfully.')
            ->assertExitCode(0);

        $this->assertSame('hotmeteor', Vault::get('org'));
        $this->assertSame('eco-cli', Vault::get('repo'));
    }
}
