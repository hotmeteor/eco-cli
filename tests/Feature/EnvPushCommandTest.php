<?php

namespace Tests\Feature;

use App\Hosts\Data\File;
use App\Support\Vault;
use Tests\TestCase;

class EnvPushCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        self::reset();

        Vault::config('org', 'hotmeteor');
        Vault::config('repo', 'eco-cli');
    }

    public function test_should_push_and_create_file()
    {
        $mock = $this->mockDriver();

        $this->mockAuthenticate($mock);

        $mock->expects($this->once())
            ->method('getSecretKey')
            ->willReturn(['key' => 'key']);

        $mock->expects($this->once())
            ->method('getRemoteFile')
            ->willReturn(new File('', null));

        $mock->expects($this->once())
            ->method('decryptContents')
            ->willReturn([]);

        $mock->expects($this->once())
            ->method('encryptContents')
            ->willReturn('encrypted');

        $mock->expects($this->once())
            ->method('createRemoteFile')
            ->willReturn(null);

        $this->artisan('env:push')
            ->expectsQuestion('What key should be pushed?', 'KEY')
            ->expectsQuestion('What is the value?', 'value')
            ->expectsOutput('The value was successfully added to the .eco file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), '');
    }

    public function test_should_push_and_create_key()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('getSecretKey')
            ->willReturn(['key' => 'key']);

        $mock->expects($this->once())
            ->method('getRemoteFile')
            ->willReturn(new File('ANOTHER=value'.PHP_EOL, 'hash'));

        $mock->expects($this->once())
            ->method('decryptContents')
            ->willReturn(['ANOTHER' => 'value']);

        $mock->expects($this->once())
            ->method('encryptContents')
            ->willReturn('encrypted');

        $mock->expects($this->once())
            ->method('updateRemoteFile')
            ->willReturn(null);

        $this->artisan('env:push')
            ->expectsQuestion('What key should be pushed?', 'KEY')
            ->expectsQuestion('What is the value?', 'value')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), '');
    }

    public function test_should_push_and_update_key_with_confirmation()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('getSecretKey')
            ->willReturn(['key' => 'key']);

        $mock->expects($this->once())
            ->method('getRemoteFile')
            ->willReturn(new File('KEY=value'.PHP_EOL, 'hash'));

        $mock->expects($this->once())
            ->method('decryptContents')
            ->willReturn(['KEY' => 'value']);

        $mock->expects($this->once())
            ->method('encryptContents')
            ->willReturn('encrypted');

        $mock->expects($this->once())
            ->method('updateRemoteFile')
            ->willReturn(null);

        $this->artisan('env:push')
            ->expectsQuestion('What key should be pushed?', 'KEY')
            ->expectsQuestion('What is the value?', 'value')
            ->expectsConfirmation('This environment key already exists. Are you sure you want to change it?', 'yes')
            ->expectsOutput('The value was successfully added to the .eco file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), '');
    }

    public function test_should_push_and_not_update_key_without_confirmation()
    {
        $mock = $this->mockDriver();

        $mock->expects($this->once())
            ->method('getSecretKey')
            ->willReturn(['key' => 'key']);

        $mock->expects($this->once())
            ->method('getRemoteFile')
            ->willReturn(new File('KEY=value'.PHP_EOL, null));

        $mock->expects($this->once())
            ->method('decryptContents')
            ->willReturn(['KEY' => 'value']);

        $mock->expects($this->never())
            ->method('encryptContents')
            ->willReturn('encrypted');

        $mock->expects($this->never())
            ->method('createRemoteFile')
            ->willReturn(null);

        $this->artisan('env:push')
            ->expectsQuestion('What key should be pushed?', 'KEY')
            ->expectsQuestion('What is the value?', 'value')
            ->expectsConfirmation('This environment key already exists. Are you sure you want to change it?', 'no')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), '');
    }
}
