<?php

namespace App\Concerns;

trait AsksForHost
{
    protected function asksForHost()
    {
        return $this->keyChoice('What code host do you use?', [
            'github' => 'Github',
            'gitlab' => 'Gitlab',
            'bitbucket' => 'Bitbucket',
        ]);
    }
}
