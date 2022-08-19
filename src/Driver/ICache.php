<?php

namespace App\Driver;

interface ICache
{
    public function init($option);

    public function add($key, $value, $expire = null);

    public function delete($key);

    public function set($key, $value, $expire = null);

    public function get($key);

    public function flush();
}
