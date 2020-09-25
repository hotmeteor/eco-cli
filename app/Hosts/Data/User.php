<?php

namespace App\Hosts\Data;

class User
{
    public $id;

    public $login;

    public function __construct($id, $login)
    {
        $this->id = $id;
        $this->login = $login;
    }
}
