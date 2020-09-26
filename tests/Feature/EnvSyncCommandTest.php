<?php

namespace Tests\Feature;

use App\Hosts\Data\File;
use App\Support\Vault;
use Tests\TestCase;

class EnvSyncCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        self::reset();

        Vault::config('org', 'hotmeteor');
        Vault::config('repo', 'eco-cli');

        Vault::unset('hotmeteor');

        Vault::set('hotmeteor.eco-cli.KEY', 'value');
        Vault::set('hotmeteor.eco-cli.THIS', 'that');
        Vault::set('hotmeteor.eco-cli.UP', 'down');
    }

    public function test_should_sync_file_and_confirm_changes()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('getRemoteFile')
            ->willReturn(new File('', null));

        $mock->expects($this->once())
            ->method('getSecretKey')
            ->willReturn(['key' => 'key']);

        $mock->expects($this->once())
            ->method('decryptContents')
            ->willReturn([
                'REMOTE' => 'value',
                'LEFT' => 'right',
                'THIS' => '',
            ]);

        $this->artisan('env:sync')
            ->expectsOutput('Syncing will use your local variables, but ask you about conflicting remote variables.')
            ->expectsConfirmation('The THIS variable already exists in your local .env. Do you want to overwrite it?', 'yes')
            ->assertExitCode(0);

        $contents =
            'KEY=value'.PHP_EOL.
            'THIS='.PHP_EOL.
            'UP=down'.PHP_EOL.
            'REMOTE=value'.PHP_EOL.
            'LEFT=right'.PHP_EOL;

        $this->assertStringEqualsFile(self::envFile(), $contents);
    }

    public function test_should_sync_file_without_confirming_changes()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('getRemoteFile')
            ->willReturn(new File('', null));

        $mock->expects($this->once())
            ->method('getSecretKey')
            ->willReturn(['key' => 'key']);

        $mock->expects($this->once())
            ->method('decryptContents')
            ->willReturn([
                'REMOTE' => 'value',
                'LEFT' => 'right',
                'THIS' => '',
            ]);

        $this->artisan('env:sync')
            ->expectsOutput('Syncing will use your local variables, but ask you about conflicting remote variables.')
            ->expectsConfirmation('The THIS variable already exists in your local .env. Do you want to overwrite it?', 'no')
            ->assertExitCode(0);

        $contents =
            'KEY=value'.PHP_EOL.
            'THIS=that'.PHP_EOL.
            'UP=down'.PHP_EOL.
            'REMOTE=value'.PHP_EOL.
            'LEFT=right'.PHP_EOL;

        $this->assertStringEqualsFile(self::envFile(), $contents);
    }

    public function test_should_sync_file_and_force_changes()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('getRemoteFile')
            ->willReturn(new File('', null));

        $mock->expects($this->once())
            ->method('getSecretKey')
            ->willReturn(['key' => 'key']);

        $mock->expects($this->once())
            ->method('decryptContents')
            ->willReturn([
                'REMOTE' => 'value',
                'LEFT' => 'right',
                'THIS' => '',
            ]);

        $this->artisan('env:sync --force')
            ->expectsOutput('Syncing will use your local variables, but ask you about conflicting remote variables.')
            ->assertExitCode(0);

        $contents =
            'KEY=value'.PHP_EOL.
            'THIS='.PHP_EOL.
            'UP=down'.PHP_EOL.
            'REMOTE=value'.PHP_EOL.
            'LEFT=right'.PHP_EOL;

        $this->assertStringEqualsFile(self::envFile(), $contents);
    }
}
