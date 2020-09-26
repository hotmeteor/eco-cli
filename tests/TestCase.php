<?php

namespace Tests;

use App\Hosts\Data\User;
use App\Hosts\HostManager;
use App\Support\Vault;
use Illuminate\Support\Str;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        config(['app.env_path' => base_path('tests/Fixtures')]);
        config(['app.home_path' => base_path('tests/Fixtures')]);
    }

    public static function envFile(): string
    {
        return config('app.env_path').'/.env';
    }

    public static function vaultFile(): string
    {
        return config('app.home_path').'/.eco/config.json';
    }

    public static function set($value): void
    {
        file_put_contents(self::envFile(), $value);
    }

    public static function reset(): void
    {
        self::set('');
    }

    protected function mockDriver($driver = 'fake')
    {
        Vault::set('driver', $driver);
        Vault::config('token', 'my-token');

        $class = '\\App\\Hosts\\Drivers\\'.Str::studly("{$driver}_driver");

        $mock = $this->createMock(get_class(new $class()));

        $manager = $this->app->get(HostManager::class);

        $manager->extend($driver, function () use ($mock) {
            return $mock;
        });

        return $mock;
    }

    protected function mockAuthenticate($mock)
    {
        $mock->expects($this->once())
            ->method('authenticate')
            ->with('my-token')
            ->willReturn(null);

        $mock->expects($this->atLeastOnce())
            ->method('getCurrentUser')
            ->willReturn(new User(1, 'hotmeteor'));
    }
}
