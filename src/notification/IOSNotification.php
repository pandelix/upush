<?php

namespace UPush\notification;

use UPush\Exceptions\InvalidArgumentException;

abstract class IOSNotification extends UmengNotification
{
    protected $iosPayload = array('aps' => array(),);

    protected $APS_KEYS = array('alert', 'badge', 'sound', 'content-available');

    public function __construct($params)
    {
        parent::__construct($params);
        $this->data['payload'] = $this->iosPayload;
    }

    public function setPredefinedKeyValue($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('key应该是一个字符串!');
        }
        if (in_array($key, $this->DATA_KEYS)) {
            $this->data[$key] = $value;
        } elseif (in_array($key, $this->APS_KEYS)) {
            $this->data['payload']['aps'][$key] = $value;
        } elseif (in_array($key, $this->POLICY_KEYS)) {
            $this->data['policy'][$key] = $value;
        } else {
            if ($key == 'payload' || $key == 'policy' || $key == 'aps') {
                throw new InvalidArgumentException('不应该设置' . $key . '的值,直接将其子级的值设置在外层即可');
            } else {
                throw new InvalidArgumentException('未知参数' . $key);
            }
        }
    }

    public function setCustomizedField($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('key应该是一个字符串!');
        }
        if (in_array($key, ['d', 'p', 'aps'])) {
            throw new InvalidArgumentException('key不能是d|p|aps!');
        }
        $this->data['payload'][$key] = $value;
    }
}