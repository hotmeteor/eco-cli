<?php

namespace App\Hosts\Data;

class Organization
{
    public $id;

    public $login;

    public function __construct($id, $login)
    {
        $this->id = $id;
        $this->login = $login;
    }
}
