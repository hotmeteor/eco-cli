<?php

namespace Tests;

use App\Hosts\FakeDriver;
use App\Hosts\HostManager;
use App\Support\Vault;
use LaravelZero\Framework\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        Vault::set('driver', 'fake');
        Vault::set('token', 'this-is-my-token');

        $manager = $this->app->get(HostManager::class);

        $manager->extend('fake', function () {
            return new FakeDriver();
        });
    }

    public static function envFile(): string
    {
        return base_path('.env');
    }

    public static function set($value): void
    {
        file_put_contents(self::envFile(), $value);
    }

    public static function reset(): void
    {
        self::set('');
    }
}
