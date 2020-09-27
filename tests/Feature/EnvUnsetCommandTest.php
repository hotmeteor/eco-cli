<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvUnsetCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::set('KEY=value');
    }

    public function test_it_unsets_with_question()
    {
        $this->artisan('env:unset')
            ->expectsQuestion('What key should be unset?', 'KEY')
            ->expectsOutput('The KEY value has been deleted and removed from your .env file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), '');
    }

    public function test_it_unsets_with_provided_key()
    {
        $this->artisan('env:unset KEY')
            ->expectsOutput('The KEY value has been deleted and removed from your .env file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), '');
    }
}
