<?php

namespace UPush;

use UPush\notification\AndroidNotification;
use UPush\notification\android\AndroidBroadcast;
use UPush\notification\android\AndroidFilecast;
use UPush\notification\android\AndroidGroupcast;
use UPush\notification\android\AndroidListcast;
use UPush\notification\ios\IOSBroadcast;
use UPush\notification\ios\IOSFilecast;
use UPush\notification\ios\IOSGroupcast;
use UPush\notification\ios\IOSListcast;
use UPush\notification\UmengNotification;

class Client
{
    private $production_mode;
    private $platform;
    private $common_keys;
    private $get_json;

    private static $EFFECTIVE_PLATFORMS = array('ios', 'android');

    public function __construct($app_infos, $platform = 'all', $test = false)
    {
        if (empty($app_infos) || !is_array($app_infos)) {
            throw new \InvalidArgumentException('appkey和secret不能为空!');
        }
        $time = strval(time());
        foreach ($app_infos as $key => $value) {
            if (!in_array($key, self::$EFFECTIVE_PLATFORMS)) {
                throw new \InvalidArgumentException('app_infos格式不正确!');
            }
            if (empty($value['appkey']) || empty($value['secret'])) {
                throw new \InvalidArgumentException($key . '的appkey和secret不能为空!');
            }
            $this->common_keys[$key] = ['appkey' => $value['appkey'], 'secret' => $value['secret'], 'timestamp' => $time];
        }
        $this->production_mode = !boolval($test);
        $this->setPlatform($platform);
    }

    public function getJson($get_json = true)
    {
        $this->get_json = $get_json;
        return $this;
    }

    public function setPlatform($platform = 'all')
    {
        if (is_string($platform)) {
            $ptf = strtolower($platform);
            if ('all' === $ptf) {
                $this->platform = self::$EFFECTIVE_PLATFORMS;
            } elseif (in_array($ptf, self::$EFFECTIVE_PLATFORMS)) {
                $this->platform = array($ptf);
            }
        } elseif (is_array($platform)) {
            $ptf = array_map('strtolower', $platform);
            $this->platform = array_intersect($ptf, self::$EFFECTIVE_PLATFORMS);
        }
        return $this;
    }

    public function test($test = true)
    {
        $this->production_mode = !boolval($test);
        return $this;
    }

    private function setCommonContent(UmengNotification $plt, $type, $data)
    {
        if ($type == 'notification') { //任务消息
            $plt->setPredefinedKeyValue('display_type', 'notification');
            if (empty(empty($data['title']) || $data['subtitle']) || empty($data['text'])) {
                throw new \InvalidArgumentException('通知内容不能为空');
            }
            if ($plt instanceof AndroidNotification) {
                $plt->setPredefinedKeyValue('display_type', 'notification');
                $plt->setPredefinedKeyValue('ticker', $data['title']);
                $plt->setPredefinedKeyValue('title', $data['subtitle']);
                $plt->setPredefinedKeyValue('text', $data['text']);
            } else {
                $plt->setPredefinedKeyValue('alert', ['title' => $data['title'], 'subtitle' => 'subtitle', 'body' => $data['text']]);
            }
        } else { //自定义消息
            if (empty($data['custom'])) {
                throw new \InvalidArgumentException('自定义字段不能为空');
            }
            if ($plt instanceof AndroidNotification) {
                $plt->setPredefinedKeyValue('display_type', 'message');
                $plt->setPredefinedKeyValue('custom', $data['custom']);
            } else {
                $plt->setCustomizedField('custom', $data['custom']);
                $plt->setPredefinedKeyValue('content-available', 1);
            }
        }
        $plt->setPredefinedKeyValue('production_mode', $this->production_mode);
        return $plt;
    }

    public function sendBroadcast($data, $type = 'notification')
    {
        if (empty($this->platform)) {
            throw new \InvalidArgumentException('发送平台错误');
        }
        try {
            if (in_array('android', $this->platform)) {
                $android = new AndroidBroadcast($this->common_keys['android']);
                $this->setCommonContent($android, $type, $data);
                $android->setPredefinedKeyValue('after_open', 'go_app');
                if ($this->get_json) {
                    return $android->getJson();
                }
                $android->send();
            }
            if (in_array('ios', $this->platform)) {
                $ios = new IOSBroadcast($this->common_keys['ios']);
                $this->setCommonContent($ios, $type, $data);
                if ($this->get_json) {
                    return $ios->getJson();
                }
                $ios->send();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    public function sendListcast($device_tokens, $data, $type = 'notification')
    {
        try {
            if (is_array($device_tokens)) {
                $count = count($device_tokens);
                $device_tokens = implode(',', $device_tokens);
            } else {
                $count = substr_count($device_tokens, ',') + 1;
            }
            if ($count > 500) {
                throw new \InvalidArgumentException('device_tokens最多支持500个');
            }
            if (in_array('android', $this->platform)) {
                $android = new AndroidListcast($this->common_keys['android']);
                $android->setPredefinedKeyValue('device_tokens', $device_tokens);
                $this->setCommonContent($android, $type, $data);
                $android->setPredefinedKeyValue('after_open', 'go_app');
                if ($this->get_json) {
                    return $android->getJson();
                }
                $android->send();
            }
            if (in_array('ios', $this->platform)) {
                $ios = new IOSListcast($this->common_keys['ios']);
                $ios->setPredefinedKeyValue('device_tokens', $device_tokens);
                $this->setCommonContent($ios, $type, $data);
                if ($this->get_json) {
                    return $ios->getJson();
                }
                $ios->send();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }

    /*
     * $filter格式:['where' => ['and' => [['tag' => 'test'], ['tag' => 'Test']]]];
     */
    public function sendGroupcast($filter, $data, $type = 'notification')
    {
        try {
            if (in_array('android', $this->platform)) {
                $android = new AndroidGroupcast($this->common_keys['android']);
                $this->setCommonContent($android, $type, $data);
                $android->setPredefinedKeyValue('filter', $filter); //设置过滤条件
                $android->setPredefinedKeyValue('after_open', 'go_app');
                if ($this->get_json) {
                    return $android->getJson();
                }
                $android->send();
            }
            if (in_array('ios', $this->platform)) {
                $ios = new IOSGroupcast($this->common_keys['ios']);
                $this->setCommonContent($ios, $type, $data);
                $ios->setPredefinedKeyValue('filter', $filter); //设置过滤条件
                if ($this->get_json) {
                    return $ios->getJson();
                }
                $ios->send();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }


    public function sendFilecast($content, $data, $type = 'notification')
    {
        try {
            if (in_array('android', $this->platform)) {
                $android = new AndroidFilecast($this->common_keys['android']);
                $this->setCommonContent($android, $type, $data);
                $android->setPredefinedKeyValue('after_open', 'go_app');
                $android->uploadContents($content);
                if ($this->get_json) {
                    return $android->getJson();
                }
                $android->send();
            }
            if (in_array('ios', $this->platform)) {
                $ios = new IOSFilecast($this->common_keys['ios']);
                $this->setCommonContent($ios, $type, $data);
                $ios->uploadContents($content);
                if ($this->get_json) {
                    return $ios->getJson();
                }
                $ios->send();
            }
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }


}