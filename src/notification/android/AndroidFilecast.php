<?php

namespace UPush\notification\android;

use UPush\notification\AndroidNotification;
use UPush\notification\FileTrait;

class AndroidFilecast extends AndroidNotification
{
    public function init()
    {
        $this->data['type'] = 'filecast';
        $this->data['file_id'] = null;
    }

    use FileTrait;

}