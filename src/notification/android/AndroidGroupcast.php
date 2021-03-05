<?php

namespace UPush\notification\android;

use UPush\notification\AndroidNotification;

class AndroidGroupcast extends AndroidNotification
{
    public function init()
    {
        $this->data['type'] = 'groupcast';
        $this->data['filter'] = null;
    }
}