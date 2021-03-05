<?php

namespace UPush\notification;

use UPush\Exceptions\InvalidArgumentException;

abstract class AndroidNotification extends UmengNotification
{
    protected $androidPayload = array(
        'display_type' => 'notification',
        'body' => array(
            'ticker' => null,
            'title' => null,
            'text' => null,
            'play_vibrate' => 'true',
            'play_lights' => 'true',
            'play_sound' => 'true',
            'after_open' => null,
        ),
    );
    protected $PAYLOAD_KEYS = array('display_type');

    protected $BODY_KEYS = array('ticker', 'title', 'text', 'builder_id', 'icon', 'largeIcon', 'img', 'play_vibrate', 'play_lights', 'play_sound', 'after_open', 'url', 'activity', 'custom');

    public function __construct($params)
    {
        parent::__construct($params);
        $this->data['payload'] = $this->androidPayload;
        $this->init();
    }

    public function setPredefinedKeyValue($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('key应该是一个字符串!');
        }

        if (in_array($key, $this->DATA_KEYS)) {
            $this->data[$key] = $value;
        } elseif (in_array($key, $this->PAYLOAD_KEYS)) {
            $this->data['payload'][$key] = $value;
            if ($key == 'display_type' && $value == 'message') {
                $this->data['payload']['body']['ticker'] = '';
                $this->data['payload']['body']['title'] = '';
                $this->data['payload']['body']['text'] = '';
                $this->data['payload']['body']['after_open'] = '';
                if (!array_key_exists('custom', $this->data['payload']['body'])) {
                    $this->data['payload']['body']['custom'] = null;
                }
            }
        } elseif (in_array($key, $this->BODY_KEYS)) {
            $this->data['payload']['body'][$key] = $value;
            if ($key == 'after_open' && $value == 'go_custom' && !array_key_exists('custom', $this->data['payload']['body'])) {
                $this->data['payload']['body']['custom'] = null;
            }
        } elseif (in_array($key, $this->POLICY_KEYS)) {
            $this->data['policy'][$key] = $value;
        } else {
            if ($key == 'payload' || $key == 'body' || $key == 'policy' || $key == 'extra') {
                throw new InvalidArgumentException('不应该设置' . $key . '的值,直接将其子级的值设置在外层即可');
            } else {
                throw new InvalidArgumentException('未知参数' . $key);
            }
        }
    }

    public function setExtraField($key, $value)
    {
        if (!is_string($key)) {
            throw new InvalidArgumentException('key应该是一个字符串!');
        }
        $this->data['payload']['extra'][$key] = $value;
    }
}