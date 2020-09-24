<?php

namespace App\Hosts\Concerns;

trait DecryptsValues
{
    protected static function decrypt($key, $cipher_text, $nonce)
    {
        $decoded_key = base64_decode($key, true);

        return sodium_crypto_secretbox_open($cipher_text, $nonce, $decoded_key);
    }
}
