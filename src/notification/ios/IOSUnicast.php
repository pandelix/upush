<?php

namespace UPush\notification\ios;

use UPush\notification\IOSNotification;

class IOSUnicast extends IOSNotification
{
    public function init()
    {
        $this->data['type'] = 'unicast';
        $this->data['device_tokens'] = null;
    }

}