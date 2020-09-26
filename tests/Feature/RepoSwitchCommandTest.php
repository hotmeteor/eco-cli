<?php

namespace Tests\Feature;

use App\Hosts\Data\Repository;
use App\Support\Vault;
use Tests\TestCase;

class RepoSwitchCommandTest extends TestCase
{
    public function test_should_switch_repo_using_id()
    {
        Vault::config('org', 'hotmeteor');
        Vault::config('repo', 'this_repo');

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

        $this->assertSame('hotmeteor', Vault::config('org'));
        $this->assertSame('other_repo', Vault::config('repo'));
    }

    public function test_should_switch_repo_using_name()
    {
        Vault::config('org', 'hotmeteor');
        Vault::config('repo', 'this_repo');

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

        $this->assertSame('hotmeteor', Vault::config('org'));
        $this->assertSame('eco-cli', Vault::config('repo'));
    }
}
