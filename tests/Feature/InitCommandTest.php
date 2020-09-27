<?php

namespace Tests\Feature;

use App\Hosts\Data\Organization;
use App\Hosts\Data\Repository;
use App\Hosts\Data\User;
use App\Hosts\HostManager;
use App\Support\Vault;
use Illuminate\Support\Str;
use Tests\TestCase;

class InitCommandTest extends TestCase
{
    public function test_it_init_with_token()
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
        $this->assertSame(Vault::config('token'), 'my-token');
        $this->assertSame(Vault::config('org'), 'hotmeteor');
        $this->assertSame(Vault::config('repo'), 'eco-cli');
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
        $this->assertSame(Vault::config('token'), 'my-token');
        $this->assertSame(Vault::config('org'), 'ecoorg');
        $this->assertSame(Vault::config('repo'), 'eco-cli');
    }

    protected function mockDriver($driver = 'fake')
    {
        file_put_contents(self::vaultFile(), '');

        $class = '\\App\\Hosts\\Drivers\\'.Str::studly("{$driver}_driver");

        $mock = $this->createMock(get_class(new $class()));

        $manager = $this->app->get(HostManager::class);

        $manager->extend($driver, function () use ($mock) {
            return $mock;
        });

        return $mock;
    }
}
