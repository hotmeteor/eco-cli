<?php

namespace Tests\Feature;

use App\Support\Vault;
use Tests\TestCase;

class InitCommandTest extends TestCase
{
    public function test_it_init_with_personal()
    {
        $mock = $this->mockDriver('github');

        Vault::unset('org');
        Vault::unset('repo');
        Vault::unset('driver');
        Vault::unset('token');

        $mock->expects($this->once())
            ->method('authenticate')
            ->with('my-token')
            ->willReturn(null);

        $mock->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn([
                'id' => 1,
                'login' => 'hotmeteor',
            ]);

        $mock->expects($this->once())
            ->method('getOrganizations')
            ->willReturn([
                ['id' => 2222, 'login' => 'hotmeteor'],
            ]);

        $mock->expects($this->once())
            ->method('getCurrentUserRepositories')
            ->willReturn([
                ['id' => 3333, 'name' => 'eco-cli'],
            ]);

        $this->artisan('init')
            ->expectsOutput('----')
            ->expectsQuestion('What code host do you use?', 'github')
            ->expectsQuestion('Token', 'my-token')
//            ->expectsOutput('To start, you will need a Github Personal Access token.')
//            ->expectsOutput('https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token')
            ->expectsQuestion('Which organization should be used?', 1)
            ->expectsOutput('Organization set successfully.')
            ->expectsChoice('Which repository should be used? You can always switch this later.', 'eco-cli', ['eco-cli'])
            ->expectsOutput('Repository set successfully.');

        $this->assertSame(Vault::get('driver'), 'github');
        $this->assertSame(Vault::get('token'), 'my-token');
        $this->assertSame(Vault::get('org'), 'hotmeteor');
        $this->assertSame(Vault::get('repo'), 'eco-cli');
    }

    public function test_it_init_with_org()
    {
        $mock = $this->mockDriver('github');

        Vault::unset('org');
        Vault::unset('repo');
        Vault::unset('driver');
        Vault::unset('token');

        $mock->expects($this->once())
            ->method('authenticate')
            ->with('my-token')
            ->willReturn(null);

        $mock->expects($this->once())
            ->method('getCurrentUser')
            ->willReturn([
                'id' => 1,
                'login' => 'ecoorg',
            ]);

        $mock->expects($this->once())
            ->method('getOrganizations')
            ->willReturn([
                ['id' => 2222, 'login' => 'hotmeteor'],
                ['id' => 1, 'login' => 'ecoorg'],
            ]);

        $mock->expects($this->once())
            ->method('getCurrentUserRepositories')
            ->willReturn([
                ['id' => 3333, 'name' => 'eco-cli'],
            ]);

        $this->artisan('init')
            ->expectsOutput('----')
            ->expectsQuestion('What code host do you use?', 'github')
            ->expectsQuestion('Token', 'my-token')
//            ->expectsOutput('To start, you will need a Github Personal Access token.')
//            ->expectsOutput('https://docs.github.com/en/github/authenticating-to-github/creating-a-personal-access-token')
            ->expectsQuestion('Which organization should be used?', 1)
            ->expectsOutput('Organization set successfully.')
            ->expectsChoice('Which repository should be used? You can always switch this later.', 'eco-cli', ['eco-cli'])
            ->expectsOutput('Repository set successfully.');

        $this->assertSame(Vault::get('driver'), 'github');
        $this->assertSame(Vault::get('token'), 'my-token');
        $this->assertSame(Vault::get('org'), 'ecoorg');
        $this->assertSame(Vault::get('repo'), 'eco-cli');
    }
}
