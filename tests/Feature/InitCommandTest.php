<?php

namespace Tests\Feature;

use App\Hosts\Data\Organization;
use App\Hosts\Data\Repository;
use App\Hosts\Data\User;
use App\Support\Vault;
use Tests\TestCase;

class InitCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Vault::unset('org');
        Vault::unset('repo');
        Vault::unset('driver');
        Vault::unset('token');
    }

    public function test_it_init_with_personal()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('authenticate')
            ->with('my-token')
            ->willReturn(null);

        $mock->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(new User(1, 'hotmeteor'));

        $mock->expects($this->once())
            ->method('getOrganizations')
            ->willReturn(
                collect([
                    new Organization(2222, 'ecoorg'),
                ])
            );

        $mock->expects($this->once())
            ->method('getCurrentUserRepositories')
            ->willReturn(
                collect([
                    new Repository(3333, 'eco-cli'),
                ])
            );

        $this->artisan('init')
            ->expectsOutput('----')
            ->expectsQuestion('What code host do you use?', 'fake')
            ->expectsQuestion('Token', 'my-token')
//            ->expectsOutput('To start, you will need a Github Personal Access token.')
//            ->expectsOutput('https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token')
            ->expectsQuestion('Which organization should be used?', 1)
            ->expectsOutput('Organization set successfully.')
            ->expectsChoice('Which repository should be used? You can always switch this later.', 'eco-cli', ['eco-cli'])
            ->expectsOutput('Repository set successfully.');

        $this->assertSame(Vault::get('driver'), 'fake');
        $this->assertSame(Vault::get('token'), 'my-token');
        $this->assertSame(Vault::get('org'), 'hotmeteor');
        $this->assertSame(Vault::get('repo'), 'eco-cli');
    }

    public function test_it_init_with_org()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('authenticate')
            ->with('my-token')
            ->willReturn(null);

        $mock->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn(new User(1, 'hotmeteor'));

        $mock->expects($this->once())
            ->method('getOrganizations')
            ->willReturn(
                collect([
                    new Organization(2222, 'ecoorg'),
                ])
            );

        $mock->expects($this->once())
            ->method('getOwnerRepositories')
            ->willReturn(
                collect([
                    new Repository(3333, 'eco-cli'),
                ])
            );

        $this->artisan('init')
            ->expectsOutput('----')
            ->expectsQuestion('What code host do you use?', 'fake')
            ->expectsQuestion('Token', 'my-token')
//            ->expectsOutput('To start, you will need a Github Personal Access token.')
//            ->expectsOutput('https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token')
            ->expectsQuestion('Which organization should be used?', 2222)
            ->expectsOutput('Organization set successfully.')
            ->expectsChoice('Which repository should be used? You can always switch this later.', 'eco-cli', ['eco-cli'])
            ->expectsOutput('Repository set successfully.');

        $this->assertSame(Vault::get('driver'), 'fake');
        $this->assertSame(Vault::get('token'), 'my-token');
        $this->assertSame(Vault::get('org'), 'ecoorg');
        $this->assertSame(Vault::get('repo'), 'eco-cli');
    }
}
