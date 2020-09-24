<?php

namespace App\Commands;

use App\Hosts\Driver;
use App\Support\Config;
use Eco\Env;
use LaravelZero\Framework\Commands\Command as ZeroCommand;

abstract class Command extends ZeroCommand
{
    protected $host;

    protected $current_user;

    protected $secret_key;

    protected $env_file = '.env';

    protected $env_example_file = '.env.example';

    protected $eco_file = '.eco';

    /**
     * Aliases for the command.
     *
     * @var string
     */
    public $aliases = [];

    protected function configure()
    {
        parent::configure();

        $this->host = app('host');

        $this->setAliases($this->aliases);
    }

    protected function driver(): Driver
    {
        return $this->host->driver();
    }

    /**
     * Ensure that the user has authenticated with Eco.
     *
     * @return void
     */
    public function authenticate()
    {
        $token = empty(Config::get('token')) ? $this->secret('Github Token') : Config::get('token');

        $this->driver()->authenticate($token);

        try {
            $this->driver()->getCurrentUser();

            Config::set('token', $token);
        } catch (\Exception $exception) {
            $this->abort(
                $exception->getMessage() === 'Bad credentials'
                    ? 'Invalid token.'
                    : $exception->getMessage()
            );
        }
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

        if (!$this->confirm('You are manipulating the production environment. Are you sure you want to proceed', false)) {
            $this->abort('Action cancelled.');
        }
    }

    public function abort($text)
    {
        $this->danger($text);

        exit(1);
    }

    public function danger($text)
    {
        $this->output->writeln('<fg=red>'.$text.'</>');
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
