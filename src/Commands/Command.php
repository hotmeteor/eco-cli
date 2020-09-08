<?php

namespace Eco\EcoCli\Commands;

use DateTime;
use Eco\EcoCli\Helpers;
use Github\Client;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    public Client $github;

    public $input;

    public $output;

    protected $startedAt;

    public $rowCount = 0;

    /**
     * Ensure that the user has authenticated with Eco.
     *
     * @return void
     */
    public function authenticate()
    {
        if (isset($_ENV['GITHUB_API_TOKEN']) ||
            getenv('GITHUB_API_TOKEN')) {
            return;
        }

        try {
            $token = Helpers::config('token') ?? Helpers::secret('Github Token');

            $this->github->authenticate(
                $token, null, \Github\Client::AUTH_ACCESS_TOKEN
            );

            Helpers::config(['token' => $token]);
        } catch (\Exception $exception) {
            throw new \Exception("Please authenticate using the 'install' command before proceeding.");
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startedAt = new DateTime();

        $this->github = Helpers::app(Client::class);

        Helpers::app()->instance('input', $this->input = $input);
        Helpers::app()->instance('output', $this->output = $output);

        $this->configureOutputStyles($output);

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

    protected function setLine($file, $key, $value)
    {
        if (!file_exists($file)) {
            file_put_contents($file, null);
        }

        $key = strtoupper(trim($key));

        $setting = $this->buildSetting($key, $value);

        $temp_file = "{$file}.tmp";

        $reading = fopen($file, 'r');
        $writing = fopen($temp_file, 'w');

        $replaced = false;

        while (!feof($reading)) {
            $line = fgets($reading);
            if (substr($line, 0, strlen($key)) === $key) {
                $line = $setting;
                $replaced = true;
            }

            fputs($writing, $line);
        }

        fclose($reading);
        fclose($writing);

        if (!$replaced) {
            $setting = $setting.PHP_EOL;

            file_put_contents($temp_file, $setting, FILE_APPEND);
        }

        rename($temp_file, $file);
    }

    protected function unsetLine($file, $key)
    {
        if (!file_exists($file)) {
            return;
        }

        $key = strtoupper(trim($key));

        $temp_file = "{$file}.tmp";

        $reading = fopen($file, 'r');
        $writing = fopen($temp_file, 'w');

        $replaced = false;

        while (!feof($reading)) {
            $line = fgets($reading);
            if (substr($line, 0, strlen($key)) === $key) {
                $line = '';
                $replaced = true;
            }

            fputs($writing, $line);
        }

        fclose($reading);
        fclose($writing);

        if (!$replaced) {
            unlink($temp_file);
        } else {
            rename($temp_file, $file);
        }
    }

    protected function buildSetting($key, $value)
    {
        if (str_contains($value, ' ')) {
            $value = "'".$value."'";
        }

        return trim($key).'='.trim($value);
    }
}
