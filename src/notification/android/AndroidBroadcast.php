<?php

namespace UPush\notification\android;

use UPush\notification\AndroidNotification;

class AndroidBroadcast extends AndroidNotification
{
    public function init()
    {
        $this->data['type'] = 'broadcast';
    }
}