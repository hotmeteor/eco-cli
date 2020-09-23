<?php

namespace Eco\EcoCli\Models;

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
