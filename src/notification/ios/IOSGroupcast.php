<?php

namespace UPush\notification\ios;

use UPush\notification\IOSNotification;


class IOSGroupcast extends IOSNotification
{
    public function init()
    {
        $this->data['type'] = 'groupcast';
        $this->data['filter'] = null;
    }

}