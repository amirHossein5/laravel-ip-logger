<?php

namespace AmirHossein5\LaravelIpLogger\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;

class Failed 
{
    use Dispatchable, SerializesModels;

    public function __construct(public $exception)
    {
        
    }
}