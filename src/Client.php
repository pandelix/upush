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
    private $production_mode = true;
    private $os = [];
    private $common_keys = [];
    private $get_json = false;
    private $result = [];
    private $receipt_url = '';
    private $receipt_type = '';

    const EFFECTIVE_OS_VALUES = ['ios', 'android'];

    public function __construct($app_infos, $os = 'all', $test = false)
    {
        $time = strval(time());
        foreach (self::EFFECTIVE_OS_VALUES as $value) {
            if (($os == 'all' || $os == $value) && empty($app_infos[$value])) {
                throw new \InvalidArgumentException($value . '的appkey和secret不能为空!');
            }
            if (!empty($app_infos[$value])) {
                $this->common_keys[$value] = $app_infos[$value];
                $this->common_keys[$value]['timestamp'] = $time;
            }
        }
        if (empty($this->common_keys)) {
            throw new \InvalidArgumentException('appkey和secret不能为空!');
        }
        $this->production_mode = !boolval($test);

        $this->setOs($os);
    }

    public function getResult()
    {
        return $this->result;
    }

    public function getJson($get_json = true)
    {
        $this->get_json = $get_json;
        return $this;
    }

    public function setOs($os = 'all')
    {
        if (empty($os)) { //自动匹配os
            foreach (self::EFFECTIVE_OS_VALUES as $value) {
                if (!empty($this->common_keys[$value])) {
                    $this->os[] = $value;
                }
            }
        } elseif ($os == 'all') {
            $this->os = self::EFFECTIVE_OS_VALUES;
        } else {
            $plt = is_string($os) ? explode(',', $os) : $os;
            $ptf = array_map('strtolower', $os);
            $this->os = array_intersect($ptf, self::EFFECTIVE_OS_VALUES);
        }
        return $this;
    }

    public function test($test = true)
    {
        $this->production_mode = !boolval($test);
        return $this;
    }

    public function setReceipt($url, $type = '')
    {
        $this->receipt_url = $url;
        $this->receipt_type = $type;
    }

    private function setCommonContent(UmengNotification $plt, $data, $type = '')
    {
        if (empty($this->os)) {
            throw new \InvalidArgumentException('发送平台错误');
        }
        if (!in_array($type, ['message', 'notification'])) {
            $type = 'message';
        }
        if ($type == 'notification' && (empty(empty($data['title']) || $data['subtitle']) || empty($data['content']))) {
            throw new \InvalidArgumentException('通知内容不能为空');
        } elseif (empty($data['custom'])) {
            throw new \InvalidArgumentException('自定义内容不能为空');
        }
        if ($plt instanceof AndroidNotification) {
            $plt->setPredefinedKeyValue('display_type', $type);
            $plt->setPredefinedKeyValue('ticker', $data['title']);
            $plt->setPredefinedKeyValue('title', $data['subtitle']);
            $plt->setPredefinedKeyValue('text', $data['content']);
            $plt->setPredefinedKeyValue('custom', $data['custom']);
        } else {
            $plt->setPredefinedKeyValue('alert', ['title' => $data['title'], 'subtitle' => 'subtitle', 'body' => $data['content']]);
            $plt->setCustomizedField('custom', $data['custom']);
            $plt->setPredefinedKeyValue('content-available', intval($type == 'message'));
        }
        $plt->setPredefinedKeyValue('production_mode', $this->production_mode);
        if ($this->receipt_url) {
            $plt->setPredefinedKeyValue('receipt_url', $this->receipt_url);
        }
        if ($this->receipt_type) {
            $plt->setPredefinedKeyValue('receipt_type', $this->receipt_type);
        }
        return $plt;
    }

    public function sendBroadcast($data, $type = 'message')
    {
        try {
            if (in_array('android', $this->os)) {
                $android = new AndroidBroadcast($this->common_keys['android']);
                $this->setCommonContent($android, $data, $type);
                $android->setPredefinedKeyValue('after_open', 'go_app');
                if ($this->get_json) {
                    return $android->getJson();
                }
                $this->result['android'] = $android->send();
            }
            if (in_array('ios', $this->os)) {
                $ios = new IOSBroadcast($this->common_keys['ios']);
                $this->setCommonContent($ios, $data, $type);
                if ($this->get_json) {
                    return $ios->getJson();
                }
                $this->result['ios'] = $ios->send();
            }
        } catch (\Exception $e) {
            $this->result['error'] = $e->getMessage();
            return false;
        }
        return true;
    }

    public function sendListcast($device_tokens, $data, $type = 'message')
    {
        try {
            if (is_array($device_tokens)) {
                $count = count($device_tokens);
                $device_tokens = implode(',', $device_tokens);
            } else {
                $count = !empty($device_tokens) ? (substr_count($device_tokens, ',') + 1) : 0;
            }
            if ($count == 0 || $count > 500) {
                throw new \InvalidArgumentException('请传入0-500个device_tokens');
            }
            if (in_array('android', $this->os)) {
                $android = new AndroidListcast($this->common_keys['android']);
                $android->setPredefinedKeyValue('device_tokens', $device_tokens);
                $this->setCommonContent($android, $data, $type);
                $android->setPredefinedKeyValue('after_open', 'go_app');
                if ($this->get_json) {
                    return $android->getJson();
                }
                $this->result['android'] = $android->send();
            }
            if (in_array('ios', $this->os)) {
                $ios = new IOSListcast($this->common_keys['ios']);
                $ios->setPredefinedKeyValue('device_tokens', $device_tokens);
                $this->setCommonContent($ios, $data, $type);
                if ($this->get_json) {
                    return $ios->getJson();
                }
                $this->result['ios'] = $ios->send();
            }
        } catch (\Exception $e) {
            $this->result['error'] = $e->getMessage();
            return false;
        }
        return true;
    }

    /*
     * $filter格式:['where' => ['and' => [['tag' => 'test'], ['tag' => 'Test']]]];
     */
    public function sendGroupcast($filter, $data, $type = 'message')
    {
        try {
            if (empty($filter)) {
                throw new \InvalidArgumentException('filter不能为空');
            }
            if (in_array('android', $this->os)) {
                $android = new AndroidGroupcast($this->common_keys['android']);
                $this->setCommonContent($android, $data, $type);
                $android->setPredefinedKeyValue('filter', $filter); //设置过滤条件
                $android->setPredefinedKeyValue('after_open', 'go_app');
                if ($this->get_json) {
                    return $android->getJson();
                }
                $this->result['android'] = $android->send();
            }
            if (in_array('ios', $this->os)) {
                $ios = new IOSGroupcast($this->common_keys['ios']);
                $this->setCommonContent($ios, $data, $type);
                $ios->setPredefinedKeyValue('filter', $filter); //设置过滤条件
                if ($this->get_json) {
                    return $ios->getJson();
                }
                $this->result['ios'] = $ios->send();
            }
        } catch (\Exception $e) {
            $this->result['error'] = $e->getMessage();
            return false;
        }
        return true;
    }


    public function sendFilecast($content, $data, $type = 'message')
    {
        try {
            if (in_array('android', $this->os)) {
                $android = new AndroidFilecast($this->common_keys['android']);
                $this->setCommonContent($android, $data, $type);
                $android->setPredefinedKeyValue('after_open', 'go_app');
                $android->uploadContents($content);
                if ($this->get_json) {
                    return $android->getJson();
                }
                $this->result['android'] = $android->send();
            }
            if (in_array('ios', $this->os)) {
                $ios = new IOSFilecast($this->common_keys['ios']);
                $this->setCommonContent($ios, $data, $type);
                $ios->uploadContents($content);
                if ($this->get_json) {
                    return $ios->getJson();
                }
                $this->result['ios'] = $ios->send();
            }
        } catch (\Exception $e) {
            $this->result['error'] = $e->getMessage();
            return false;
        }
        return true;
    }

}
