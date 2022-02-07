<?php

namespace AmirHossein5\LaravelIpLogger\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static self detailsBe(array|\Closure $details)
 * @method static self prepare(\Closure $details)
 * @method static bool|array getDetails()
 * @method static null|string|\Exception getLastException()
 * @method static self model(string $model)
 * @method static bool|Illuminate\Database\Eloquent\Model UpdateOrCreate(\Closure $attributes, \Closure $values)
 * @method static bool|Illuminate\Database\Eloquent\Model create(\Closure $values)
 **/

class IpLogger extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'ipLogger';
    }
}