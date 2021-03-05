<?php

namespace UPush\notification\android;

use UPush\notification\AndroidNotification;
use UPush\notification\FileTrait;
use UPush\Exceptions\InvalidArgumentException;

class AndroidCustomizedcast extends AndroidNotification
{

    public function init()
    {
        $this->data['type'] = 'customizedcast';
        $this->data['alias_type'] = null;
    }

    public function isComplete()
    {
        parent::isComplete();
        if (!array_key_exists('alias', $this->data) && !array_key_exists('file_id', $this->data)) {
            throw new InvalidArgumentException('alias或file_id必须被设置!');
        }
    }

    use FileTrait;


}