<?php

namespace App\Driver;

class CMemcached implements ICache
{

    private static $_instance = null;
    private $mc = null;
    private $host = '127.0.0.1';
    private $port = 11211;
    private $expire = 86400;

    private function __construct($option = null)
    {
        $this->host = isset($option->host) && !empty($option->host) ? $option->host : $this->host;
        $this->port = isset($option->port) && !empty($option->port) ? $option->port : $this->port;
        $this->expire = isset($option->expire) && !empty($option->expire) ? $option->expire : $this->expire;

        $this->init($option);
    }

    static public function getInstance($option)
    {
        if (is_null(self::$_instance) || isset (self::$_instance)) {
            self::$_instance = new self($option);
        }
        return self::$_instance;
    }

    public function init($option)
    {
        try {
            $this->mc = new Memcached;
            $this->mc->addServer($this->host, $this->port);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    public function add($key, $value, $expire = null)
    {
        return $this->mc->add($key, $value, is_null($expire) ? $this->expire : $expire);
    }

    public function delete($key)
    {
        return $this->mc->delete($key);
    }

    public function set($key, $value, $expire = null)
    {
        return $this->mc->set($key, $value, is_null($expire) ? $this->expire : $expire);
    }

    public function get($key)
    {
        return $this->mc->get($key);
    }

    public function flush()
    {
        return $this->mc->flush();
    }
}
