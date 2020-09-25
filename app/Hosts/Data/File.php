<?php

namespace App\Hosts\Data;

class File
{
    public $contents;

    public $hash;

    public function __construct($contents, $hash)
    {
        $this->contents = $contents;

        $this->hash = $hash;
    }
}
