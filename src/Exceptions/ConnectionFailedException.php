<?php

namespace AmirHossein5\LaravelIpLogger\Exceptions;

use Exception;

class ConnectionFailedException extends Exception
{
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
