<?php

namespace Eco\EcoCli\Commands;

use Eco\EcoCli\Config;
use Eco\EcoCli\Helpers;
use Symfony\Component\Console\Input\InputOption;

class EnvSetCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('env:set')
            ->setAliases(['set'])
            ->addOption('key', null, InputOption::VALUE_OPTIONAL, 'The key of the value to set')
            ->addOption('push', 'P', InputOption::VALUE_OPTIONAL, 'If the value should also be pushed')
            ->addOption('hidden', 'H', InputOption::VALUE_OPTIONAL, 'If the value should be encrypted')
            ->setDescription('Store a local variable that syncs with remote .env.');
    }

    public function handle()
    {
        if (!empty($this->option('key'))) {
            $key = $this->option('key');
        } else {
            $key = strtoupper(trim(Helpers::ask('Key')));
        }

        $value = Helpers::ask('Value');
        $repo = Config::get('repo');

        Config::set("local.{$repo}.{$key}", trim($value));

        $this->setLine('.env', $key, $value);

        Helpers::line('<info>The</info> <comment>'.$key.'</comment> <info>value has been stored and added to your .env file.</info>');
    }
}
