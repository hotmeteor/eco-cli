<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Helpers;
use Symfony\Component\Console\Input\InputOption;

class EnvUnsetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('env:unset')
            ->setAliases(['unset'])
            ->addOption('key', null, InputOption::VALUE_OPTIONAL, 'The key of the value to unset')
            ->setDescription('Unset and delete a local variable');
    }

    public function handle()
    {
        if (!empty($this->option('key'))) {
            $key = $this->option('key');
        } else {
            $key = strtoupper(trim(Helpers::ask('Key')));
        }

        $value = Helpers::ask('Value');
        $repo = Helpers::config('repo');

        Helpers::config("local.{$repo}.{$key}", trim($value));

        $this->unsetLine('.env', $key);

        Helpers::line('<info>The</info> <comment>'.$key.'</comment> <info>value has been deleted and removed from your .env file.</info>');
    }
}
