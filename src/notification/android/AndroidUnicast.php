<?php

namespace UPush\notification\android;

use UPush\notification\AndroidNotification;

class AndroidUnicast extends AndroidNotification
{
    public function init()
    {
        $this->data['type'] = 'unicast';
        $this->data['device_tokens'] = null;
    }

}