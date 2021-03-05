<?php

namespace UPush\notification\ios;

use UPush\notification\FileTrait;
use UPush\notification\IOSNotification;

class IOSFilecast extends IOSNotification
{
    public function init()
    {
        $this->data['type'] = 'filecast';
        $this->data['file_id'] = null;
    }

    use FileTrait;

}