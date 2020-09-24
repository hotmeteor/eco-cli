<?php

namespace App\Commands;

use App\Support\Vault;

class EnvSyncCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'env:sync
                            {--F|force : Automatically accept all remote values}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Sync remote variables with your local values.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->info('Syncing will use your local variables, but ask you about conflicting remote variables.');

        $this->authenticate();

        $org = Vault::get('org');
        $repo = Vault::get('repo');

        $this->task('Settings up file', function () {
            return $this->setupFile();
        });

        $this->task('Assigning local values', function () use ($org, $repo) {
            return $this->assignLocalValues($org, $repo);
        });

        $this->task('Assigning remote values', function () use ($org, $repo) {
            return $this->assignRemoteValues($org, $repo);
        });

        $this->line("<info>Your .env file has been synced.</info>");
    }

    protected function setupFile()
    {
        if (!file_exists($this->envFile())) {
            file_put_contents($this->envFile(), '', true);
        }

        return true;
    }

    protected function assignLocalValues($org, $repo): bool
    {
        $data = Vault::get("{$org}.{$repo}") ?? [];

        foreach ($data as $key => $value) {
            $this->setLine($this->envFile(), $key, $value);
        }

        return true;
    }

    protected function assignRemoteValues($owner, $repo)
    {
        $file = $this->driver()->getRemoteFile(
            $owner, $repo, $this->vaultFile()
        );

        $data = $this->driver()->decryptContents(
            $file->contents, $this->driver()->getSecretKey($owner, $repo)
        );

        foreach ($data as $key => $value) {
            if (!$this->option('force') && $this->findLine($this->envFile(), $key)) {
                if ($this->confirm("The {$key} variable already exists in your local .env. Do you want to overwrite it?")) {
                    $this->set($key, $value);
                }
            } else {
                $this->set($key, $value);
            }
        }

        return true;
    }

    protected function set($key, $value)
    {
        $this->setLine($this->envFile(), $key, $value);
    }
}
