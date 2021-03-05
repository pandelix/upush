<?php

namespace UPush\Exceptions;

class InvalidArgumentException extends \InvalidArgumentException
{

    public function __construct($message)
    {
        $message = '参数有误! ' . $message;
        parent::__construct($message);
    }

    public function __toString()
    {
        return PHP_EOL . __CLASS__ . " -- [{$this->code}]: {$this->message} " . PHP_EOL;
    }
}
