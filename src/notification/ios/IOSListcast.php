<?php

namespace UPush\notification\ios;

use UPush\notification\IOSNotification;

class IOSListcast extends IOSNotification
{
    public function init()
    {
        $this->data['type'] = 'listcast';
        $this->data['device_tokens'] = null;
    }

}