<?php

namespace AmirHossein5\LaravelIpLogger;

use AmirHossein5\LaravelIpLogger\Exceptions\ConnectionFailedException;
use Illuminate\Support\Facades\Http;
use Symfony\Component\Routing\Exception\InvalidParameterException;

trait GetDetailsFrom
{
    private function vpn_api(string $ip): ?array
    {
        if (! config('ipLogger.vpn_api_key')) {
            throw new InvalidParameterException('No key found in config for using vpnapi.');
        }
        
        $api = Http::get('https://vpnapi.io/api/' . $ip . "?key=" . config('ipLogger.vpn_api_key'))->object();

        if (! $api) {
            throw new ConnectionFailedException('vpnapi.io not working.');
        }

        return (array) $api;
    }

    private function ip_api(string $ip): ?array
    {
        $api = Http::get("http://ip-api.com/php/". $ip ."?fields=status,message,continent,country,countryCode,region,regionName,city,zip,lat,lon,timezone,currency,isp,org,as,mobile,proxy,hosting,query");

        $api = unserialize($api->body());
        
        if (! $api) {
            throw new ConnectionFailedException('ip-api.com not working.');
        }

        return $api;
    }
}