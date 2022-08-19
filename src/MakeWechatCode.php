<?php /** @noinspection ALL */

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

    /**
     * 构造
     *
     * @param $appid
     * @param $appSecret
     * @param $dir
     * @param $driver
     * @param $host
     * @param $port
     * @param $auth
     * @param $expire
     */
    public function __construct($appid, $appSecret, $dir = '', $driver = '', $host = '', $port = '', $auth = '', $expire = '')
    {
        parent::__construct();
        $this->setAppId($appid);
        $this->setAppSecret($appSecret);
        $this->setDir($dir);
        $this->setDriver($driver);
        if (in_array($driver, ['redis', 'memcache', 'memcached'])) $this->setCacheHost($host)->setCacheAuth($port)->setCachePort($auth);
        $this->setCacheExpire($expire);
        $this->setGetTokenUrl();
        $this->buildCache();
        $this->getAccessToken();
        $this->setGetQrUrl();
    }

    /**
     * 创建小程序二维码
     *
     * @param $scene
     * @param $page
     * @param $width
     * @param $env_version
     * @param $check_path
     * @param $auto_color
     * @param $is_hyaline
     * @return string|void
     */
    public function create($scene, $page, $width = '', $env_version = '', $check_path = '', $auto_color = '', $is_hyaline = '')
    {
        $this->setScene($scene);
        $this->setPage($page);
        $this->setWidth($width);
        $this->setEnvVersion($env_version)->setCheckPath($check_path);
        $this->setAutoColor($auto_color)->setIsHyaline($is_hyaline);
        $name = md5(time() . uniqid()) . '.png';
        $resources = self::post($this->qr_url, json_encode($this->wx_config));
        if (strpos($resources, 'errcode') === false) {
            $this->save($name, $resources);
            return $this->dir . $name;
        } else {
            $resources = json_decode($resources, true);
            self::showJson($resources['errcode'], $resources['errmsg']);
        }

    }

    /**
     * @param $is_hyaline
     * @return $this
     */
    public function setIsHyaline($is_hyaline)
    {
        if (!empty($is_hyaline)) return $this;
        $this->wx_config['is_hyaline'] = $scene;
        return $this;
    }

    /**
     * @param $auto_color
     * @return $this
     */
    public function setAutoColor($auto_color)
    {
        if (!empty($auto_color)) return $this;
        $this->wx_config['auto_color'] = $scene;
        return $this;
    }

    /**
     * @param $check_path
     * @return $this
     */
    public function setCheckPath($check_path)
    {
        if (!empty($check_path)) return $this;
        $this->wx_config['check_path'] = $scene;
        return $this;
    }

    /**
     * @param $env_version
     * @return $this
     */
    public function setEnvVersion($env_version)
    {
        if (!empty($env_version)) return $this;
        $this->wx_config['env_version'] = $scene;
        return $this;
    }

    /**
     * @param $scene
     * @return bool
     */
    public function setScene($scene): bool
    {
        if (empty($scene)) return self::showJson(9000, 'scene not null');
        $this->wx_config['scene'] = $scene;
        return true;
    }

    /**
     * @param $page
     * @return bool
     */
    public function setPage($page): bool
    {
        if (empty($page)) return self::showJson(9000, 'page not null');
        $this->wx_config['page'] = $page;
        return true;
    }

    /**
     * @param $name
     * @param $resources
     * @return false|void
     */
    public function save($name, $resources = null)
    {
        if (!$name) return false;
        if (!is_dir($this->dir)) mkdir($this->dir) && chmod($this->dir, 0777);
        $filename = $this->dir . $name;
        $file = fopen($filename, 'w+');
        fwrite($file, $resources);
        fclose($file);
    }

    /**
     * @return bool
     */
    private function getAccessToken(): bool
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
        return true;
    }


    /**
     * 设置微信 appid
     *
     * @param $appid
     * @return bool
     */
    private function setAppId($appid): bool
    {
        if (empty($appid)) return self::showJson(9000, 'appid not null');
        $this->appid = $appid;
        return true;
    }

    /**
     * @param $appSecret
     * @return bool
     */
    private function setAppSecret($appSecret): bool
    {
        if (empty($appSecret)) return self::showJson(9000, 'appSecret not null');
        $this->appSecret = $appSecret;
        return true;
    }

    /**
     * @param $dir
     * @return MakeWechatCode
     */
    private function setDir($dir): MakeWechatCode
    {
        if (empty($dir)) return $this;
        $this->dir = $dir;
        return $this;
    }

    /**
     * @param $driver
     * @return MakeWechatCode
     */
    private function setDriver($driver): MakeWechatCode
    {
        if (empty($driver)) return $this;
        $this->config['driver'] = in_array("C" . ucfirst(strtolower($driver)), ['CMemcache', 'CMemcached', 'CRedis', 'CSession']) ? "App\Driver\\" . $driver : 'App\Driver\CSession';
        return $this;
    }

    /**
     * @param $host
     * @return MakeWechatCode
     */
    private function setCacheHost($host): MakeWechatCode
    {
        if (empty($host)) return $this;
        $this->config['host'] = $host;
        return $this;
    }

    /**
     * @param $auth
     * @return MakeWechatCode
     */
    private function setCacheAuth($auth): MakeWechatCode
    {
        if (empty($auth)) return $this;
        $this->config['auth'] = $auth;
        return $this;
    }

    /**
     * @param $port
     * @return MakeWechatCode
     */
    private function setCachePort($port): MakeWechatCode
    {
        if (empty($port)) return $this;
        $this->config['port'] = $port;
        return $this;
    }

    /**
     * @param $expire
     * @return MakeWechatCode
     */
    private function setCacheExpire($expire): MakeWechatCode
    {
        if (empty($expire)) return $this;
        $this->config['expire'] = $expire;
        return $this;
    }

    /**
     * @return bool
     */
    private function buildCache(): bool
    {
        $this->cache = ($this->config['driver'])::getInstance($this->config);
        return true;
    }

    /**
     * @param $width
     * @return MakeWechatCode
     */
    private function setWidth($width): MakeWechatCode
    {
        if (!empty($width)) return $this;
        $this->wx_config['width'] = $width;
        return $this;
    }

    /**
     * @return bool
     */
    protected function setGetTokenUrl(): bool
    {
        $this->token_url = self::TOKEN_URL . $this->appid . '&secret=' . $this->appSecret;
        return true;
    }

    /**
     * @return boll
     */
    protected function setGetQrUrl(): boll
    {
        $this->qr_url = self::QR_CODE_URL . "{$this->access_token}";
        return true;
    }

    /**
     * @param $url
     * @param $param
     * @return bool|string
     */
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