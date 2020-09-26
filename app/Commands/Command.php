<?php

namespace App\Commands;

use App\Hosts\HostManager;
use App\Support\KeyChoiceQuestion;
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
        return $this->host->driver(Vault::get('driver'));
    }

    public function envFile()
    {
        return config('app.env_path').'/'.$this->env_file;
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
        try {
            $credentials = $this->getCredentials();
            call_user_func_array([$this->driver(), 'authenticate'], $credentials);
            $this->current_user = $this->currentUser();
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

    protected function currentUser()
    {
        return $this->current_user ?? $this->driver()->getCurrentUser();
    }

    protected function getCredentials(): array
    {
        return ($driver = $this->askForHost()) === 'bitbucket'
            ? $this->askForUsernamePassword()
            : $this->askForToken();
    }

    protected function askForHost()
    {
        if (!empty(Vault::get('driver'))) {
            return Vault::get('driver');
        }

        $driver = $this->keyChoice('What code host do you use?', [
            'github' => 'Github',
            'gitlab' => 'Gitlab',
            'bitbucket' => 'Bitbucket',
        ]);

        Vault::set('driver', $driver);

        return $driver;
    }

    protected function askForToken()
    {
        $token = Vault::config('token');

        if (empty($token)) {
            Vault::config('token', $token = $this->secret('Token'));
        }

        return [$token, null];
    }

    protected function askForUsernamePassword()
    {
        $username = Vault::config('username');
        $password = Vault::config('password');

        if (empty($username) || empty($password)) {
            Vault::config('username', $username = $this->ask('Username', $username));
            Vault::config('password', $password = $this->secret('Password'));
        }

        return [$username, $password];
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

    public function keyChoice($title, $choices)
    {
        return $this->output->askQuestion(
            new KeyChoiceQuestion($title, $choices)
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
