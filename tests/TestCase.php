<?php

namespace Tests;

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

        Vault::set('org', 'my_org');
        Vault::set('repo', 'my_repo');
        Vault::set('token', 'this-is-my-token');
    }

    public static function envFile(): string
    {
        return config('app.env_path').'/.env';
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

        $class = '\\App\\Hosts\\'.Str::studly("{$driver}_driver");

        $mock = $this->createMock(get_class(new $class()));

        $manager = $this->app->get(HostManager::class);

        $manager->extend($driver, function () use ($mock) {
            return $mock;
        });

        return $mock;
    }
}
