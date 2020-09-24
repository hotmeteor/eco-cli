<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvUnsetCommandTest extends TestCase
{
    public function test_it_should_ask_and_set_value()
    {
        self::set('KEY=value');

        $this->artisan('env:unset')
            ->expectsQuestion('What key should be unset?', 'KEY')
            ->expectsOutput('The KEY value has been deleted and removed from your .env file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), '');
    }

    public function test_it_should_set_value()
    {
        self::set('KEY=value');

        $this->artisan('env:unset KEY')
            ->expectsOutput('The KEY value has been deleted and removed from your .env file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), '');
    }
}
