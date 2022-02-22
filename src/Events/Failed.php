<?php

namespace AmirHossein5\LaravelIpLogger\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Failed
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(public $exception)
    {
    }
}
