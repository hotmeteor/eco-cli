<?php

namespace Eco\EcoCli\Hosts\Concerns;

trait SecuresFileContents
{
    use DecryptsValues;
    use EncryptsValues;

    public function decryptContents($contents, $public_key): array
    {
        $data = json_decode(base64_decode($contents, true));

        $values = base64_decode($data->values, true);
        $nonce = base64_decode($data->nonce, true);

        return json_decode(self::decrypt($public_key, $values, $nonce), true);
    }

    public function encryptContents(array $values, $public_key)
    {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        return json_encode([
            'values' => base64_encode(self::encrypt($public_key, json_encode($values), $nonce)),
            'nonce' => base64_encode($nonce),
        ]);
    }
}
