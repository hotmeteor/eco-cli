<?php

namespace App\Commands;

use App\Support\Vault;
use App\Support\Helpers;
use Illuminate\Support\Arr;

class EnvPushCommand extends Command
{
    protected $values = [];

    protected $hash;

    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'env:push
                            {key? : The key of the value to push (optional)}
                            {--S|set : Also set the value locally (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Push a variable to the remote repository.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->authenticate();

        $owner = Vault::get('org');
        $repo = Vault::get('repo');

        $key = $this->asksForKey();

        $value = $this->ask('What is the value?');

        $this->setSecretKey($owner, $repo);

        if ($this->keyExists($owner, $repo, $key)) {
            if ($this->confirm('This environment key already exists. Are you sure you want to change it?')) {
                $this->setValue($owner, $repo, $key, $value);
            }
        } else {
            $this->setValue($owner, $repo, $key, $value);
        }
    }

    protected function asksForKey()
    {
        if (empty($key = $this->argument('key'))) {
            $key = $this->ask('What key should be pushed?');
        }

        return strtoupper(trim($key));
    }

    protected function setSecretKey($owner, $repo): void
    {
        $this->secret_key = $this->driver()->getSecretKey($owner, $repo);
    }

    protected function keyExists($owner, $repo, $key): bool
    {
        try {
            $file = $this->driver()->getRemoteFile(
                $owner, $repo, $this->vaultFile()
            );

            $decrypted = $this->driver()->decryptContents(
                $file->contents, $this->secret_key['key']
            );

            $this->hash = $file->hash;
            $this->values = $decrypted;

            return array_key_exists($key, $decrypted);
        } catch (\Exception $exception) {
            $this->values = [];

            return false;
        }
    }

    protected function setValue($owner, $repo, $key, $value): void
    {
        $payload = $this->driver()->encryptContents(
            Arr::set($this->values, $key, $value),
            $this->secret_key
        );

        try {
            if ($this->hash) {
                $this->driver()->updateRemoteFile(
                    $owner, $repo, $this->vaultFile(), $payload, 'Update .eco values', $this->hash
                );
            } else {
                $this->driver()->createRemoteFile(
                    $owner, $repo, $this->vaultFile(), $payload, 'Create initial .eco file'
                );
            }

            if ($this->option('set')) {
                Vault::set("{$owner}.{$repo}.{$key}", $value);
            }

            $this->info(Helpers::exclaim().'! The value was successfully added to the .eco file.');
        } catch (\Exception $exception) {
            $this->abort('There was an issue updating the .eco file.');
        }
    }
}
