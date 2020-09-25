<?php

namespace App\Commands;

use App\Support\Vault;

class RepoSwitchCommand extends Command
{
    /**
     * The signature of the command.
     *
     * @var string
     */
    protected $signature = 'repo:switch
                            {--name= : The repo name (optional)}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Switch to a different repo context.';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->authenticate();

        $repos = Vault::get('org') === $this->current_user['login']
            ? $this->driver()->getCurrentUserRepositories()
            : $this->driver()->getOwnerRepositories(Vault::get('org'));

        $repo_id = null;

        if (!empty($this->option('name'))) {
            $repo = collect($repos)->where('name', $this->option('name'))->first();

            if (empty($repo)) {
                $this->abort('Repo not found.');
            }

            $repo_id = $repo['id'];
        }

        if (is_null($repo_id)) {
            $repo_id = $this->choice(
                'Which repository should be used? You can always switch this later.',
                collect($repos)->sortBy->name->mapWithKeys(function ($repo) {
                    return [$repo['id'] => $repo['name']];
                })->all()
            );
        }

        $key = is_numeric($repo_id) ? 'id' : 'name';

        Vault::set('repo', collect($repos)->firstWhere($key, $repo_id)['name']);

        $this->info('Repository set successfully.');
    }
}
