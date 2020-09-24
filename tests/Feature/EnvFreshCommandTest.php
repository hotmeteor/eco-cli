<?php

namespace Tests\Feature;

use App\Hosts\FakeDriver;
use App\Hosts\HostManager;
use App\Models\File;
use App\Support\Vault;
use Illuminate\Container\Container;
use Tests\TestCase;

class EnvFreshCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Vault::set('org', 'my_org');
        Vault::set('repo', 'my_repo');
    }

    public function test_should_not_store_fresh_env_when_not_confirmed()
    {
        $this->artisan('env:fresh')
            ->expectsConfirmation('Are you sure you want a fresh .env? This will overwrite your existing .env file.', 'no')
            ->assertExitCode(0);
    }

    public function test_should_store_fresh_env()
    {
        $mock = $this->createMock(HostManager::class);

        $mock->expects($this->once())->method('driver')->willReturnCallback(function () {
            $driver = $this->createMock(FakeDriver::class);
            $driver->expects($this->once())->method('getRemoteFile')->willReturn(new File('KEY=value', 'hash'));

            return $driver;
        });

        $this->app[Container::class]->instance(HostManager::class, $mock);

        $this->artisan('env:fresh')
            ->expectsConfirmation('Are you sure you want a fresh .env? This will overwrite your existing .env file.', 'yes')
            ->assertExitCode(0);
    }
}
