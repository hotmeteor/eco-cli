<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Config;
use Eco\EcoCli\Helpers;
use Symfony\Component\Console\Input\InputArgument;

class EnvSetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('env:set')
            ->setAliases(['set'])
            ->addArgument('key', InputArgument::OPTIONAL, 'The key of the value to set')
            ->setDescription('Set and store a local variable');
    }

    public function handle()
    {
        if (!empty($this->argument('key'))) {
            $key = $this->argument('key');
        } else {
            $key = strtoupper(trim(Helpers::ask('Key')));
        }

        $value = Helpers::ask('Value');
        $repo = Helpers::config('repo');

        Config::set("local.{$repo}.{$key}", trim($value));

        $this->setLine('.env', $key, $value);

        Helpers::line('<info>The</info> <comment>'.$key.'</comment> <info>value has been stored and added to your .env file.</info>');
    }
}
