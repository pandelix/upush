<?php

namespace UPush\notification\android;

use UPush\notification\AndroidNotification;

class AndroidListcast extends AndroidNotification
{
    public function init()
    {
        $this->data['type'] = 'listcast';
        $this->data['device_tokens'] = null;
    }

}