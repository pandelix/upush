<?php

namespace UPush\notification\ios;

use UPush\notification\IOSNotification;

class IOSBroadcast extends IOSNotification
{
    public function init()
    {
        $this->data['type'] = 'broadcast';
    }

}