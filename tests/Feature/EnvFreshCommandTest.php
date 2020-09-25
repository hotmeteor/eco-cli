<?php

namespace Tests\Feature;

use App\Models\File;
use Tests\TestCase;

class EnvFreshCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::set('MY=value1234'.PHP_EOL);
    }

    public function test_should_not_refresh_without_confirmation()
    {
        $this->artisan('env:fresh')
            ->expectsConfirmation('Are you sure you want a fresh .env? This will overwrite your existing .env file.', 'no')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), 'MY=value1234'.PHP_EOL);
    }

    public function test_should_refresh_with_confirmation()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('getRemoteFile')
            ->willReturn(new File('KEY=value'.PHP_EOL, 'hash'));

        $this->artisan('env:fresh')
            ->expectsConfirmation('Are you sure you want a fresh .env? This will overwrite your existing .env file.', 'yes')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), 'KEY=value'.PHP_EOL);
    }
}
