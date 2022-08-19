<?php

namespace App;

class MakeWechatCode extends Base
{
    const TOKEN_URL = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=';
    const QR_CODE_URL = 'https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token=';
    protected $cache;
    protected $appid;
    protected $appSecret;
    protected $config = [
        'host' => '127.0.0.1',
        'port' => '3306',
        'auth' => '',
        'expire' => 86400,
        'driver' => 'App\Driver\CSession'
    ];
    protected $access_token;
    protected $wx_config = [
        'scene' => '',
        'page' => '',
        'width' => '480px',
        'auto_color' => false,
        'is_hyaline' => false,
        'check_path' => true,
        'env_version' => 'release'
    ];
    protected $token_url;
    protected $qr_url;
    protected $dir;

    public function __construct($appid, $appSecret, $dir = '', $width = '', $driver = '', $host = '', $port = '', $auth = '', $expire = '')
    {
        parent::__construct();
        $object = $this->setAppId($appid)->setAppSecret($appSecret);
        if ($dir) $object = $object->setDir($dir);
        if ($width) $object = $object->setWidth($width);
        if ($driver) $object = $object->setDriver($driver);
        if ($host) $object = $object->setCacheHost($host);
        if ($port) $object = $object->setCacheAuth($port);
        if ($auth) $object = $object->setCachePort($auth);
        if ($expire) $object = $object->setCacheExpire($expire);
        $object->setGetTokenUrl()->buildCache()->getAccessToken()->setGetQrUrl();
    }

    public function create($scene, $page)
    {
        $this->wx_config['scene'] = $scene;
        $this->wx_config['page'] = $page;
        $name = md5(time() . uniqid()) . '.png';
        $resources = self::post($this->qr_url, json_encode($this->wx_config));
        if (strpos($resources,'errcode') === false) {
            $this->save($name, $resources);
            return $this->dir.$name;
        } else {
            $resources = json_decode($resources, true);
            self::showJson($resources['errcode'], $resources['errmsg']);
        }

    }

    public function save($name, $resources = null)
    {
        if (!$name) return false;
        if (!is_dir($this->dir)) mkdir($this->dir) && chmod($this->dir,0777);
        $filename =  $this->dir. $name;
        $file = fopen($filename, 'w+');
        fwrite($file, $resources);
        fclose($file);
    }

    private function getAccessToken(): MakeWechatCode
    {
        $cacheKey = 'jw_applet_accessToken';
        $access_token = $this->cache->get($cacheKey);
        if (!$access_token) {
            $html = file_get_contents($this->token_url);
            $output = json_decode($html, true);
            $access_token = $output['access_token'];
            $this->cache->set('jw_applet_accessToken', $access_token, $output['expires_in'] - 240);
        }
        $this->access_token = $access_token;
        return $this;
    }


    private function setAppId($appid): MakeWechatCode
    {
        $this->appid = $appid;
        return $this;
    }

    private function setAppSecret($appSecret): MakeWechatCode
    {
        $this->appSecret = $appSecret;
        return $this;
    }

    private function setDir($dir): MakeWechatCode
    {
        $this->dir = $dir;
        return $this;
    }

    private function setDriver($driver): MakeWechatCode
    {
        $this->config['driver'] = in_array($driver, ['CMemcache', 'CMemcached', 'CRedis', 'CFilecache']) ? "App\Driver\\" . $driver : '\Driver\CFilecache';
        return $this;
    }

    private function setCacheHost($host): MakeWechatCode
    {
        $this->config['host'] = $host;
        return $this;
    }

    private function setCacheAuth($auth): MakeWechatCode
    {
        $this->config['auth'] = $auth;
        return $this;
    }

    private function setCachePort($port): MakeWechatCode
    {
        $this->config['port'] = $port;
        return $this;
    }

    private function setCacheExpire($expire): MakeWechatCode
    {
        $this->config['expire'] = $expire;
        return $this;
    }

    private function buildCache(): MakeWechatCode
    {
        $this->cache = ($this->config['driver'])::getInstance($this->config);
        return $this;
    }

    private function setPage($page): MakeWechatCode
    {
        $this->wx_config['page'] = $page;
        return $this;
    }

    private function setWidth($width): MakeWechatCode
    {
        $this->wx_config['width'] = $width;
        return $this;
    }

    protected function setGetTokenUrl(): MakeWechatCode
    {
        $this->token_url = self::TOKEN_URL . $this->appid . '&secret=' . $this->appSecret;
        return $this;
    }

    protected function setGetQrUrl(): MakeWechatCode
    {
        $this->qr_url = self::QR_CODE_URL . "{$this->access_token}";
        return $this;
    }

    static private function post($url, $param)
    {
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $param);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }
}