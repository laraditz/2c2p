<?php

namespace Laraditz\Twoc2p\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class BackendReceived
{
    use Dispatchable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }
}
