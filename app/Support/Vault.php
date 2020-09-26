<?php

namespace App\Support;

use Illuminate\Support\Arr;

class Vault
{
    protected static $instance;

    public static $root;

    /**
     * Get the given configuration value.
     *
     * @param string $key
     * @param mixed $default
     *
     * @return string|array|null
     */
    public static function get($key, $default = null)
    {
        return Arr::get(static::load(), self::buildKey($key), $default);
    }

    /**
     * Store the given configuration value.
     *
     * @param string $key
     * @param mixed $value
     *
     * @return void
     */
    public static function set($key, $value)
    {
        $config = static::load();

        Arr::set($config, self::buildKey($key), trim($value));

        file_put_contents(static::path(), json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Remove the given configuration key.
     *
     * @param string $key
     *
     * @return void
     */
    public static function unset($key)
    {
        $config = static::load();

        Arr::forget($config, self::buildKey($key));

        file_put_contents(static::path(), json_encode($config, JSON_PRETTY_PRINT));
    }

    /**
     * Load the entire configuration array.
     *
     * @return array
     */
    public static function load()
    {
        if (!is_dir(dirname(static::path()))) {
            mkdir(dirname(static::path()), 0755, true);
        }

        if (file_exists(static::path())) {
            return json_decode(file_get_contents(static::path()), true);
        }

        return [];
    }

    /**
     * @param $key
     * @param null $value
     * @return array|string|null
     */
    public static function config($key, $value = null)
    {
        $driver = self::get('driver');

        if ($value) {
            self::set("drivers.{$driver}.{$key}", $value);
        } else {
            return self::get("drivers.{$driver}.{$key}");
        }
    }

    /**
     * Get the path to the configuration file.
     *
     * @return string
     */
    protected static function path()
    {
        return config('app.home_path').'/.eco/config.json';
    }

    /**
     * Namespace key as necessary.
     *
     * @param $key
     * @return mixed|string
     */
    protected static function buildKey($key)
    {
        return self::$root ? implode('.', [self::$root, $key]) : $key;
    }
}
