<?php

namespace App\Driver;

class CSession implements ICache
{
    private static $_instance = null;

    public function __construct($option)
    {
        $this->init($option);
    }


    static public function getInstance($option): CSession
    {
        if (is_null(self::$_instance) || isset (self::$_instance)) {
            self::$_instance = new self($option);
        }
        return self::$_instance;
    }

    public function init($option)
    {
        session_start();
    }

    public function add($key, $value, $expire = null): bool
    {
        $_SESSION[$key]['value'] = $value;
        $_SESSION[$key]['expire'] = time() + $expire;
        return true;
    }

    public function delete($key): bool
    {
        unset($_SESSION[$key]);
        return true;
    }

    public function set($key, $value, $expire = null): bool
    {
        $_SESSION[$key]['value'] = $value;
        $_SESSION[$key]['expire'] = time() + $expire;
        return true;
    }

    public function get($key)
    {
        if (isset($_SESSION[$key]) && $_SESSION[$key]['expire'] > time()) {
            return $_SESSION[$key]['value'];
        }
        $this->delete($key);
    }

    public function flush(): bool
    {
        session_unset();
        session_destroy();
        return true;
    }
}