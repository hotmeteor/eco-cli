<?php

namespace App\Commands;

use App\Hosts\Driver;
use App\Hosts\HostManager;
use App\Support\Vault;
use Eco\Env;
use Github\Exception\InvalidArgumentException;
use LaravelZero\Framework\Commands\Command as ZeroCommand;

abstract class Command extends ZeroCommand
{
    protected $host;

    protected $current_user;

    protected $secret_key;

    protected $env_file = '.env';

    protected $env_example_file = '.env.example';

    protected $vault_file = '.eco';

    /**
     * Aliases for the command.
     *
     * @var string
     */
    public $aliases = [];

    protected function configure()
    {
        parent::configure();

        $this->host = app(HostManager::class);

        $this->setAliases($this->aliases);
    }

    protected function driver()
    {
        return $this->host->driver();
    }

    public function envFile()
    {
        return $this->env_file;
    }

    public function vaultFile()
    {
        return $this->vault_file;
    }

    /**
     * Ensure that the user has authenticated with Eco.
     *
     * @return void
     */
    public function authenticate()
    {
        $token = empty(Vault::get('token')) ? $this->secret('Github Token') : Vault::get('token');

        $this->driver()->authenticate($token);

        try {
            $this->driver()->getCurrentUser();

            Vault::set('token', $token);
        } catch (InvalidArgumentException $exception) {
            $this->abort($exception->getMessage());
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
