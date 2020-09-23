<?php

namespace Eco\EcoCli\Commands;

use DateTime;
use Eco\EcoCli\Helpers;
use Eco\EcoCli\Hosts\BitbucketDriver;
use Eco\EcoCli\Hosts\GithubDriver;
use Eco\EcoCli\Hosts\GitlabDriver;
use Eco\EcoCli\Hosts\HostManager;
use Eco\Env;
use Illuminate\Container\Container;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    public $host;

    public $input;

    public $output;

    protected $startedAt;

    public $rowCount = 0;

    protected $env_file = '.env';

    protected $env_example_file = '.env.example';

    protected $eco_file = '.eco';

    /**
     * Ensure that the user has authenticated with Eco.
     *
     * @return void
     */
    public function authenticate()
    {
        $token = Helpers::config('token') ?? Helpers::secret('Github Token');

        $this->host->authenticate($token);

        Helpers::config(['token' => $token]);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startedAt = new DateTime();

        Helpers::app()->instance('input', $this->input = $input);
        Helpers::app()->instance('output', $this->output = $output);

        $this->configureOutputStyles($output);
        $this->configureHost(Helpers::app());

        return Helpers::app()->call([$this, 'handle']) ?: 0;
    }

    /**
     * Configure the output styles for the application.
     *
     * @return void
     */
    protected function configureOutputStyles(OutputInterface $output)
    {
        $output->getFormatter()->setStyle(
            'finished',
            new OutputFormatterStyle('green', 'default', ['bold'])
        );
    }

    protected function configureHost(Container $app)
    {
        $app->singleton('host', function ($app) {
            return new HostManager($app);
        });

        $host = app('host');

        $host->extend('github', function ($app) {
            return new GithubDriver($app);
        });

        $host->extend('gitlab', function ($app) {
            return new GitlabDriver($app);
        });

        $host->extend('bitbucket', function ($app) {
            return new BitbucketDriver($app);
        });

        $this->host = $host;
    }

    /**
     * Get an argument from the input list.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function argument($key)
    {
        return $this->input->getArgument($key);
    }

    /**
     * Get an option from the input list.
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function option($key)
    {
        return $this->input->getOption($key);
    }

    /**
     * Ensure the user intends to manipulate the production environment.
     *
     * @param string $environment
     * @param bool $force
     *
     * @return void
     */
    protected function confirmIfProduction($environment, $force = null)
    {
        if (($this->input->hasOption('force') &&
                $this->option('force')) ||
            $environment !== 'production') {
            return;
        }

        if (!Helpers::confirm('You are manipulating the production environment. Are you sure you want to proceed', false)) {
            Helpers::abort('Action cancelled.');
        }
    }

    /**
     * Format input into a textual table.
     *
     * @param string $style
     *
     * @return void
     */
    public function table(array $headers, array $rows, $style = 'borderless')
    {
        Helpers::table($headers, $rows, $style);
    }

    /**
     * Format input to textual table, remove the prior table.
     *
     * @return void
     */
    protected function refreshTable(array $headers, array $rows)
    {
        if ($this->rowCount > 0) {
            Helpers::write(str_repeat("\x1B[1A\x1B[2K", $this->rowCount + 4));
        }

        $this->rowCount = count($rows);

        $this->table($headers, $rows);
    }

    /**
     * Create a selection menu with the given choices.
     *
     * @param string $title
     * @param array $choices
     *
     * @return mixed
     */
    public function menu($title, $choices)
    {
        return Helpers::menu($title, $choices);
    }

    /**
     * Get the ID of an item by name.
     *
     * @param string $name
     *
     * @return int
     */
    protected function findIdByName(array $items, $name, $attribute = 'name')
    {
        return collect($items)->first(function ($item) use ($name, $attribute) {
            return $item[$attribute] === $name;
        })['id'] ?? null;
    }

    /**
     * Call another console command.
     *
     * @param string $command
     *
     * @return int
     */
    public function call($command, array $arguments = [])
    {
        $arguments['command'] = $command;

        return $this->getApplication()->find($command)->run(
            new ArrayInput($arguments),
            Helpers::app('output')
        );
    }

    protected function findLine($file, $key)
    {
        return Env::has($file, $key);
    }

    protected function setLine($file, $key, $value)
    {
        Env::set($file, $key, $value);
    }

    protected function unsetLine($file, $key)
    {
        Env::unset($file, $key);
    }
}
