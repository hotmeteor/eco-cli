<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvSetCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        self::reset();
    }

    public function test_it_sets_with_question()
    {
        $this->artisan('env:set')
            ->expectsQuestion('What key should be set?', 'KEY')
            ->expectsQuestion('What is the value?', 'value')
            ->expectsOutput('The KEY value has been stored and added to your .env file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), 'KEY=value'.PHP_EOL);
    }

    public function test_it_sets_with_provided_key()
    {
        $this->artisan('env:set KEY')
            ->expectsQuestion('What is the value?', 'value')
            ->expectsOutput('The KEY value has been stored and added to your .env file.')
            ->assertExitCode(0);

        $this->assertStringEqualsFile(self::envFile(), 'KEY=value'.PHP_EOL);
    }
}
