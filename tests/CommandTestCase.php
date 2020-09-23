<?php

namespace Eco\EcoCli\Tests;

use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;
use ReflectionClass;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Tester\CommandTester;

abstract class CommandTestCase extends TestCase
{
    protected $commandClass;
    protected $signature;
    protected $commandTest;
    protected $commandOptions;
    protected $runImmediately = false;

    public function setUp(): void
    {
        parent::setUp();

        if ($this->runImmediately) {
            $this->runCommand();
        }
    }

    public function file($contents = null)
    {
        $file = __DIR__.'/.env';

        if (!is_null($contents)) {
            return file_put_contents($file, $contents);
        } else {
            return file_get_contents($file);
        }
    }

    public function runCommand($commandClass = '', $signature = '', $options = [])
    {
        $commandClass = ($commandClass) ?: $this->commandClass;
        $signature = ($signature) ?: $this->signature;
        $options = ($options) ?: ($this->commandOptions) ?: [];

        $application = new Application();
        $appCommand = $this->app->make($commandClass);
//        $appCommand->setLaravel(app());

        $application->add($appCommand);

        $command = $application->find($signature);

        $commandTester = new CommandTester($command);

        $arguments = array_merge(['command' => $command->getName()], $options);

        $commandTester->execute($arguments);

        $this->commandTest = $commandTester;
    }

    public function runCommandWithOptions($options = [])
    {
        $this->runCommand('', '', $options);
    }

    public function assertOutputEquals($string)
    {
        $string .= "\n";
        $this->assertEquals($string, $this->commandTest->getDisplay());
    }

    public function assertOutputContains($string)
    {
        $output = $this->commandTest->getDisplay();
        if (!str_contains($output, $string)) {
            $this->fail('Failed asserting that the output contains the string, "'.$string."'");
        }
    }

    public function assertOutputContainsTimes($string, $times = 1)
    {
        $output = $this->commandTest->getDisplay();

        $lastPosition = 0;
        $positions = [];

        while (($lastPosition = strpos($output, $string, $lastPosition)) !== false) {
            $positions[] = $lastPosition;
            $lastPosition = $lastPosition + strlen($string);
        }

        if (count($positions) !== $times) {
            $failure = "Failed asserting that the output contains the string, '{$string}' {$times} ".Str::plural('time', $times).".\n";
            $failure .= 'Found '.count($positions).' '.Str::plural('time', count($positions));
            $this->fail($failure);
        }
    }

    public function assertOutputDoesNotContain($string)
    {
        $output = $this->commandTest->getDisplay();
        if (str_contains($output, $string)) {
            $this->fail('Failed asserting that the output does not contain the string, "'.$string."'");
        }
    }

    public function getCommandOutput()
    {
        return $this->commandTest->getDisplay();
    }

    public function invokeCommandMethod($methodName, array $parameters = [], $commandInput = [])
    {
        $input = new ArrayInput($commandInput);
        $output = new StreamOutput(fopen('php://memory', 'w', false));

        $appCommand = $this->app->make($this->commandClass);
        $appCommand->setLaravel(app());
        $appCommand->run($input, $output);

        return $this->invokeMethod($appCommand, $methodName, $parameters);
    }

    public function invokeMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
