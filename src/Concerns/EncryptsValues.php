<?php

namespace Eco\EcoCli\Concerns;

trait EncryptsValues
{
    protected static function encrypt($key, $value, $nonce)
    {
        $decoded_key = base64_decode($key, true);

        return sodium_crypto_secretbox($value, $nonce, $decoded_key);
    }
}
