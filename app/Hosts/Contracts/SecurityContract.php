<?php

namespace App\Hosts\Contracts;

interface SecurityContract
{
    public function decryptContents($contents, $public_key): array;

    public function encryptContents(array $values, $public_key);
}
