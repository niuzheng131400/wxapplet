# php-wx-applet
Qr code of wechat mini program generated based on PHP

# Use

- 构造方法请求参数

| 属性    | 类型 | 必填 | 说明                                                                                      |
| --------- | ------ | ---- | ------------------------------------------------------------------------------------------- |
| appid     | string | 是  | 小程序appid                                                                              |
| appSecret | string | 是  | 小程序appSecret                                                                          |
| dir       | string | 否  | 生成的小程序二维码保存路径                                                     |
| driver    | string | 否  | 保存access_token使用的缓存驱动,默认使用session，可选值有 (redis,memcache,memcached,session) |
| host      | string | 否  | 当driver是redis,memcache,memcached其一时，可以填写驱动服务的IP,默认为127.0.0.1 |
| port      | number | 否  | 当driver是redis时,默认值为6379；当memcache,memcached其一时，默认值为11211  |
| auth      | string | 否  | 当driver是redis时，驱动密码默认为无                                             |
| expire    | number | 否  | 过期时间，默认值为0                                                                |

- 方法create参数

| 属性      | 类型 | 必填 | 说明                                                                                                                                                                            |
| ----------- | ------ | ---- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| scene       | string | 是  | 最大32个可见字符，只支持数字，大小写英文以及部分特殊字符：!#$&'()*+,/:;=?@-._~，其它字符请自行编码为合法字符（因不支持%，中文无法使用 urlencode 处理，请使用其他编码方式） |
| page        | string | 是  | 默认是主页，页面 page，例如 pages/index/index，根路径前不要填加 /，不能携带参数（参数请放在 scene 字段里），如果不填写这个字段，默认跳主页面。 |
| check_path  | bool   | 否  | 默认是true，检查page 是否存在，为 true 时 page 必须是已经发布的小程序存在的页面（否则报错）；为 false 时允许小程序未发布或者 page 不存在， 但page 有数量上限（60000个）请勿滥用。 |
| env_version | string | 否  | 要打开的小程序版本。正式版为 "release"，体验版为 "trial"，开发版为 "develop"。默认是正式版。                                                      |
| width       | number | 否  | 默认430，二维码的宽度，单位 px，最小 280px，最大 1280px                                                                                                         |
| auto_color  | bool   | 否  | 自动配置线条颜色，如果颜色依然是黑色，则说明不建议配置主色调，默认 false                                                                         |
| line_color  | object | 否  | 默认是{"r":0,"g":0,"b":0} 。auto_color 为 false 时生效，使用 rgb 设置颜色 例如 {"r":"xxx","g":"xxx","b":"xxx"} 十进制表示                                   |
| is_hyaline  | bool   | 否  | 默认是false，是否需要透明底色，为 true 时，生成透明底色的小程序                                                                                         |

```php
<?php

require_once './vendor/autoload.php';

use App\MakeWechatCode;

$makeWechatCode = new MakeWechatCode('wx1ec23b474bcdafrc2a7d','d72223494f7f76b22sx007e3f0ab0094e4fc3d82','./images/');

$path = $makeWechatCode->create('175211811301','pages/call/call');

exit($path);
?>
```