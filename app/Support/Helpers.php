<?php

namespace App\Support;

use Illuminate\Support\Arr;

class Helpers
{
    /**
     * Get a random exclamation.
     *
     * @return string
     */
    public static function exclaim()
    {
        return Arr::random([
            'Amazing',
            'Awesome',
            'Beautiful',
            'Boom',
            'Cool',
            'Done',
            'Got it',
            'Great',
            'Magic',
            'Nice',
            'Sweet',
            'Wonderful',
            'Yes',
        ]);
    }

    /**
     * Format .env key.
     *
     * @param $key
     * @return string
     */
    public static function formatKey($key)
    {
        return strtoupper(trim($key));
    }

    /**
     * Format .env value.
     *
     * @param $value
     * @return string
     */
    public static function formatValue($value)
    {
        $value = trim($value);

        if (str_contains($value, ' ')) {
            $value = "'{$value}'";
        }

        return $value;
    }
}
