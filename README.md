# UPush API PHP Client

这是 UPush REST API 的 PHP 版本封装开发包。

对应的 REST API 文档: https://developer.umeng.com/docs/67966/detail/68343

> 支持的 PHP 版本: 5.6.x, 7.x

## Installation

#### 使用 Composer 安装

- 在项目中的 `composer.json` 文件中添加 upush 依赖：

```json
"require": {
    "pandelix/upush": "*"
}
```

- 执行 `$ php composer.phar install` 或 `$ composer install` 进行安装。

#### 初始化

```php
...
use UPush\Client as Upush;
...

$app_infos = [
    'android' => ['appkey' => 'xx', 'secret' => 'xx'],
    'ios' => ['appkey' => 'xx', 'secret' => 'xx']
];
$client = new Upush($app_infos);
$ret = $client->test()->sendListcast('xx,xx', ['custom' => 'xxxxx'], 'message');
if ($ret === true) {
    echo '发送成功' . PHP_EOL;
} else {
    echo $ret;
}

...
```

## License

The library is available as open source under the terms of the [MIT License](http://opensource.org/licenses/MIT).
