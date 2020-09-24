<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvSetCommandTest extends TestCase
{
    public function test_it_should_ask_and_set_value()
    {
        self::reset();

        $this->artisan('env:set')
            ->expectsQuestion('What key should be set?', 'KEY')
            ->expectsQuestion('What is the value?', 'value')
            ->expectsOutput('The KEY value has been stored and added to your .env file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), 'KEY=value'.PHP_EOL);
    }

    public function test_it_should_set_value()
    {
        self::reset();

        $this->artisan('env:set KEY')
            ->expectsQuestion('What is the value?', 'value')
            ->expectsOutput('The KEY value has been stored and added to your .env file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), 'KEY=value'.PHP_EOL);
    }
}
