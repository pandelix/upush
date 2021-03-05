<?php

namespace UPush\notification;

use GuzzleHttp\Client;
use UPush\Exceptions\HttpException;
use UPush\Exceptions\InvalidArgumentException;

abstract class UmengNotification
{
    protected $host = 'http://msg.umeng.com';
    protected $uploadPath = '/upload'; //文件上传api
    protected $postPath = '/api/send'; //消息发送api
    protected $appMasterSecret = null;

    protected $data = array(
        'appkey' => null,
        'timestamp' => null,
        'type' => null,
        'production_mode' => 'true',
    );

    protected $DATA_KEYS = array('appkey', 'timestamp', 'type', 'device_tokens', 'alias', 'alias_type', 'file_id', 'filter', 'production_mode', 'feedback', 'description', 'thirdparty_id');
    protected $POLICY_KEYS = array('start_time', 'expire_time', 'max_send_num');

    public function __construct($common_keys)
    {
        if (!empty($common_keys['appkey'])) {
            $this->setPredefinedKeyValue('appkey', $common_keys['appkey']);
        }
        if (!empty($common_keys['secret'])) {
            $this->setAppMasterSecret($common_keys['secret']);
        }
        if (!empty($common_keys['timestamp'])) {
            $this->setPredefinedKeyValue('timestamp', $common_keys['timestamp']);
        }

        $this->init();
    }

    public function setAppMasterSecret($secret)
    {
        $this->appMasterSecret = $secret;
    }

    public function isComplete()
    {
        if (is_null($this->appMasterSecret)) {
            throw new InvalidArgumentException('请设置appMasterSecret!');
        }
        $this->checkArrayValues($this->data);
        return true;
    }

    private function checkArrayValues($arr)
    {
        foreach ($arr as $key => $value) {
            if (is_null($value)) {
                throw new InvalidArgumentException($key . ' 为空!');
            } elseif (is_array($value)) {
                $this->checkArrayValues($value);
            }
        }
    }

    abstract public function setPredefinedKeyValue($key, $value);

    public function send()
    {
        $this->isComplete();
        $url = $this->host . $this->postPath;
        $postBody = json_encode($this->data);
        $sign = md5("POST" . $url . $postBody . $this->appMasterSecret);
        $url = $url . "?sign=" . $sign;
        try {
            $client = new Client();
            $response = $client->request("POST", $url, [
                "body" => $postBody,
            ]);
            return json_decode($response->getBody()->getContents(), true);
        } catch (\Exception $e) {
            throw new HttpException($e->getMessage(), $e->getCode());
        }
    }

    public function getJson()
    {
        $this->isComplete();
        return json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
}
