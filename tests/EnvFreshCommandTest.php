<?php

namespace Eco\EcoCli\Tests;

use Eco\EcoCli\Commands\EnvFreshCommand;

class EnvFreshCommandTest extends CommandTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->commandClass = EnvFreshCommand::class;
        $this->signature = 'env:fresh';
    }

    /** @test */
    public function it_should_fresh_on_confirmation()
    {
        $this->file('KEY=value');

        $this->runCommand();

        $this->assertSame('', $this->file());
    }
}
