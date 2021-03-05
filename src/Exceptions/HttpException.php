<?php

namespace UPush\Exceptions;

class HttpException extends \Exception
{

    public function __construct($message = '', $code = 0)
    {
        $message = '请求错误! ' . $message . PHP_EOL;
        parent::__construct($message, $code);
    }

    public function __toString()
    {
        return __CLASS__ . " -- [{$this->code}]: {$this->message} " . PHP_EOL;
    }
}
